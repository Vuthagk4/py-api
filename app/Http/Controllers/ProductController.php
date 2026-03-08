<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Services\FCMService;
use Illuminate\Http\Request;
// 🟢 FIX 1: Use the correct Facade for database storage
use Illuminate\Support\Facades\Notification; 
use Illuminate\Support\Facades\Log; 
use App\Notifications\NewProductNotification;

class ProductController extends Controller
{
    protected $fcmService;

    // 🟢 Injecting FCMService to handle notifications
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Store a newly created product and notify users.
     */
    public function store(Request $request)
    {
         $request->validate([
        'name'        => 'required|string|max:255',
        'price'       => 'required|numeric',
        'category_id' => 'required|exists:categories,id',
        'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'sizes'       => 'nullable|array', // 🟢
        'sizes.*'     => 'string|in:XS,S,M,L,XL,XXL', // 🟢 validate each size
    ]);

        $imagePath = $request->hasFile('image')
        ? $request->file('image')->store('products', 'public')
        : null;

    $product = Product::create([
        'name'          => $request->name,
        'description'   => $request->description,
        'price'         => $request->price,
        'image'         => $imagePath,
        'is_featured'   => $request->is_featured ?? 0,
        'category_id'   => $request->category_id,
        'shopkeeper_id' => $request->user()->id,
        'stock'         => $request->stock ?? 0,
        'sizes'         => $request->sizes ?? ['S', 'M', 'L', 'XL'], // 🟢 default sizes
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Product created. Notifications are being processed.',
        'product' => $product
    ], 201);

        // 🟢 FIX 2: This is what makes the Flutter list NOT empty
        try {
            $users = User::where('role', 'user')->get();
            // This triggers the 'database' channel in your NewProductNotification
            Notification::send($users, new NewProductNotification($product));
        } catch (\Exception $e) {
            Log::error("Database Notification failed: " . $e->getMessage());
        }

        // 🟢 FIX 3: Push Notification (Pop-up alert)
        try {
            $imageUrl = $product->image ? asset('storage/' . $product->image) : null;
            $this->fcmService->sendToTopic(
                'all_users', 
                '🆕 New Product Alert!', 
                "{$product->name} is now available!",
                ['product_id' => (string) $product->id, 'type' => 'product_update'],
                $imageUrl
            );
        } catch (\Exception $e) {
            Log::error("FCM Notification failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'product' => $product], 201);
    }

    /**
     * Display a listing of categories and featured products.
     */
    public function index()
    {
        // Get all categories with their products and shopkeeper info
        $categories = Category::with(['products.shopkeeper'])->get();

        // Get ONLY products where is_featured is 1 (True)
        $featuredProducts = Product::where('is_featured', true)
            ->with('shopkeeper')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts
        ], 200);
    }
    public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->validate([
        'sizes'   => 'nullable|array',
        'sizes.*' => 'string|in:XS,S,M,L,XL,XXL',
    ]);

    $product->update([
        'name'        => $request->name ?? $product->name,
        'description' => $request->description ?? $product->description,
        'price'       => $request->price ?? $product->price,
        'stock'       => $request->stock ?? $product->stock,
        'sizes'       => $request->sizes ?? $product->sizes,
        'is_featured' => $request->is_featured ?? $product->is_featured,
    ]);

    return response()->json(['success' => true, 'product' => $product]);
}

    /**
     * Get products by a specific Category ID.
     */
    public function getProductByCate($cateId)
    {
        $category = Category::find($cateId);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
        
        $products = $category->products()->with('shopkeeper')->paginate(10);
        return response()->json($products);
    }

    /**
     * Search products by name or price range.
     */
    public function search(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
        ]);

        $query = Product::with('shopkeeper')->whereNotNull('shopkeeper_id');

        $searchTerm = $request->input('name') ?? $request->input('search');

        if (!empty($searchTerm)) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }

    /**
     * Get feedback history for the authenticated user.
     */
    public function getUserFeedback(Request $request) {
        $data = Feedback::where('user_id', $request->user()->id)
                        ->with('product') 
                        ->latest()
                        ->get();
                        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}