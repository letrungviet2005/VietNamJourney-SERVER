<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    function addProduct(Request $request)
    {
        $product = new Product;
        $product->name = $request->input('name');
        return $request->input();
    }
}
