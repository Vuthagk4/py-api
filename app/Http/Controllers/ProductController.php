<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Services\FCMService;
use Illuminate\Support\Facades\Storage;

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
        $categories = Category::with(['products'])->latest()->get(['id', 'name']);

        $featuredProducts = Product::where('is_featured', 1)
            ->limit(5)
            ->get(['id', 'name', 'description', 'price', 'image']);

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts
        ]);
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
        // 1. FIXED: Made 'name' nullable so users can search ONLY by price
        $request->validate([
            'name' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
        ]);

        // 2. FIXED: Build the query dynamically
        $query = Product::query();

        // Apply name filter only if a name was typed
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Apply min_price filter only if a minimum price was entered
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Apply max_price filter only if a maximum price was entered
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->get();

        return response()->json($products);
    }
}