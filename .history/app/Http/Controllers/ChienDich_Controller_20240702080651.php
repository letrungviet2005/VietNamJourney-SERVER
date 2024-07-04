<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChienDich;

class ChienDich_Controller extends Controller
{
    function list() {
        return ChienDich::all();
    }
}
