<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function login()
    {

        $data['username'] = '';
        $data['first'] = true;
        return view('auth/login', $data);
    }

    public function authenticate(Request $request)
    {
        if (Auth::check()) return redirect('/home');

        $userName = $request->input('userName');
        $passWord = $request->input('passWord');

        $data['first'] = true;

        if (isset($userName)) {
            if (Auth::attempt(array('USER_MAIL' => $userName, 'password' => $passWord, 'USER_USTP_ID' => 1), true)) {
                return redirect('/home');
            } else {
                $data['first'] = false;
                $data['username'] = $userName;
                return view('auth/login', $data);
            }
        } else {
            redirect("login");
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!Auth::check()) return redirect('/login');
        return view('home');
    }

    public function logout(){
        Auth::logout();
        return redirect('login');
    }
}
