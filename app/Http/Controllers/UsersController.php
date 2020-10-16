<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function _construct(){
        $this->middleware('auth');
    }

    public function home(){
        echo "tamam";
    }
}
