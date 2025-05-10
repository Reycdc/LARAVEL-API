<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use PhpParser\Node\Stmt\TryCatch;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::with('category')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function setProduct(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'required|string',
            'stock' => 'required|integer',
            'price' => 'required|numeric'
        ]);
    
        try {
            $product = Product::create($request->all());
    
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk gagal dibuat.'
                ], 500);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dibuat.',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat produk.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'required|string',
            'stock' => 'required|integer',
            'price' => 'required|numeric'
        ]);

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return response()->json($product->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
