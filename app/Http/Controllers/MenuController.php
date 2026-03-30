<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MenuController extends Controller
{
    public function index()
    {
        $userProfile = Session::get('glpi_user_profile');
        return view('menus', compact('userProfile'));
    }
}
