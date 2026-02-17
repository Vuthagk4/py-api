<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name'=> 'required|string|max:255'
        ]);

        $category = Category::create($request->all());

        return response()->json(
            [
                'message'=> "Category created successful"
            ],200
        );
        
    }
    public function update(Request $request,$id){
        $cate = Category::find($id);
        if(!$cate){
            return response()->json([
                'message'=> "product not found"
            ],404);
        }

        $cate->update($request->all());

        return response()->json(
            [
                'success'=> true,
                'category'=>$cate
            ]
        );


        
    }
    public function index(){
        $categories = Category::all();
        return response()->json([
            'success'=> true,
            'data'=> $categories
        ]);
    }

    public function destroy($id){
        $category = Category::find($id); // Find the category by ID if it exists
        if(!$category){
            return response()->json([
                'message'=> "product not found"
            ],404);
        }

        $category->delete();

        return response()->json([
            'success'=> true,
            'message'=> "Category deleted successfully"
        ]);
    }
}
