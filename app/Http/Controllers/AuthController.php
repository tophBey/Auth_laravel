<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index(){
        return view('Auth.index');
    }


    public function store(Request $request){

       $request->validate([
            'email' => 'email|required|max:50',
            'password' => 'required',
       ]);

       if(Auth::attempt($request->only('email', 'password'),$request->remember)){
          $request->session()->regenerate();
          
          if(Auth::user()->role === 'customer') {
            return redirect()->route('customer');
          }

         return redirect('/dashboard');
       }

       return back()->with('failed', 'username or password wrong');

    }


    public function dashboard(){
        
        return view('Dashboard.index');
    }


    public function register(){
        return view('Register.index');
    }

    public function registerStore(Request $request){

        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:password',
        ]);

        $request['status'] = "verify";
        $request['role'] = "customer";

        // dd($request->all());
       $user =  User::create($request->all());

        Auth::login($user);
        // return redirect()->route('login')->with('login', 'create akun succes, now login');

        return redirect('/customer');
    }

    public function logout(Request $request){
          Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();


        return redirect()->route('login');
    }

    
}
