<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App

class UserController extends Controller
{
    function register(Request $request)
    {
        return $request->input();
    }
}
