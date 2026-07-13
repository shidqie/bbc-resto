<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::with('category')->where('is_active', true)->orderBy('name')->get();

        return view('pos.index', compact('categories', 'products'));
    }
}
