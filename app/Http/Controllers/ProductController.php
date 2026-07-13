<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('service_type') && $request->service_type != '') {
            $type = $request->service_type;
            if (in_array($type, ['dine_in', 'catering', 'nasi_box'])) {
                $query->where("is_{$type}", true);
            }
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();
        
        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_dine_in'] = $request->has('is_dine_in');
        $validated['is_catering'] = $request->has('is_catering');
        $validated['is_nasi_box'] = $request->has('is_nasi_box');

        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_dine_in'] = $request->has('is_dine_in');
        $validated['is_catering'] = $request->has('is_catering');
        $validated['is_nasi_box'] = $request->has('is_nasi_box');

        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Optional: delete image on hard delete, but since we use soft deletes, keep it.
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Menu berhasil dihapus.');
    }
}
