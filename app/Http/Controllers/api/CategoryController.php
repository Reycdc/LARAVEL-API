<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function setCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function getCategory(string $id)
    {
        $category = Category::find($id); 
        if (!$category) return response()->json(['message' => 'Not Found'], 404);

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCategory(Request $request, string $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Not Found'], 404);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteCategory(string $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Not Found'], 404);

        $category->delete();
        return response()->json(['message' => 'Berhasil menghapus']);
    }
}
