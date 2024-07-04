<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App

class ProductController extends Controller
{
    function addProduct(Request $request)
    {
        return $request->input();
    }
}
