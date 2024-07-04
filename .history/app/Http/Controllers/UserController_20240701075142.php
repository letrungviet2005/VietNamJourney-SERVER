<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    function register(Request $request)
    {
        $user = new User;
        
        return $request->input();
    }
}
