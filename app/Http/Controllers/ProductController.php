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

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

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
            // 🔥 CRITICAL: Explicitly use the 'public' disk
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'is_featured' => $request->is_featured ?? 0,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

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

    public function getProductByCate($cateId)
    {
        $category = Category::find($cateId);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
        
        $products = $category->products()->paginate(10);
        return response()->json($products);
    }

    public function search(Request $request)
{
    // 1. Validation
    $request->validate([
        'name' => 'nullable|string|max:255',
        'search' => 'nullable|string|max:255', // Added to accept 'search' key
        'min_price' => 'nullable|numeric',
        'max_price' => 'nullable|numeric',
    ]);

    // 2. Load shopkeeper and ensure products belong to a shop
    $query = Product::with('shopkeeper')->whereNotNull('shopkeeper_id');

    // 3. Catch search term from either 'name' or 'search' parameter
    $searchTerm = $request->input('name') ?? $request->input('search');

    if (!empty($searchTerm)) {
        $query->where('name', 'like', '%' . $searchTerm . '%');
    }

    // Apply min_price filter
    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    // Apply max_price filter
    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    $products = $query->get();

    // Standardized response
    return response()->json([
        'success' => true,
        'data' => $products
    ], 200);
}

public function getUserFeedback(Request $request) {
    // This fetches feedback for the logged-in user and includes product info
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