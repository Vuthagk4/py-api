<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Services\FCMService;
use Illuminate\Support\Facades\Storage;
use App\Models\Feedback;

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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            // 🔥 Explicitly use the 'public' disk for CentOS storage visibility
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Create the product in MySQL
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'is_featured' => $request->is_featured ?? 0,
            'category_id' => $request->category_id,
            // Ensure shopkeeper_id is set if your model requires it
            'shopkeeper_id' => $request->user()->id, 
        ]);

        // 🟢 TRIGGER NOTIFICATION: Notify all users subscribed to 'all_users' topic
        try {
            $this->fcmService->sendToTopic(
                'all_users', 
                'New Product in Shop! 👕', 
                "Check out the new '{$product->name}' just added to our collection."
            );
        } catch (\Exception $e) {
            // Log error but don't stop the product creation
            \Log::error("FCM Notification failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created and notification sent',
            'product' => $product
        ], 201);
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