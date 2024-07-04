<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;

class ChienDich_Controller extends Controller
{
    function list()
    {
        $chienDich = Campaign::all();
        return $chienDich;
    }
}
