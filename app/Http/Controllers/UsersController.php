<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function _construct()
    {
        $this->middleware('auth');
    }

    public function home($type = 0)
    {
        $this->initDataArray($type);

        return view('users.home', $this->data);
    }


    ////////data functions
    protected $data;

    private function initDataArray($type)
    {
        if ($type == 0) {
            $this->data['users'] = User::all();
        } else {
            $this->data['users'] = User::where('USER_USTP_ID', $type)->get();
        }
    }
}
