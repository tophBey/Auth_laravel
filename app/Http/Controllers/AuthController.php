<?php

namespace App\Http\Controllers;

use App\Mail\OtpEmail;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

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

    public function google_redirect(){
         return Socialite::driver('google')->redirect();
    }

    public function google_callback(){

        $googleUser = Socialite::driver('google')->user();
        $user = User::where('email', $googleUser->email)->first();

        if(!$user){
           $user =  User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'status' => 'active'
            ]);
        }

        if($user && $user->status === 'banned'){
            return redirect()->route('login')->with('banned', 'Akun Anda Dibekukan');
        }

        if($user && $user->status === 'verify'){
            $user->update([
                'status' => 'active'
            ]);
        }

        Auth::login($user);
        if($user->role === 'customer'){
            return redirect()->route('customer');
        }
    }


    public function resetPassword(){
        return view('Auth.resetPassword');
    }

    public function resetPasswordStore(Request $request){

        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return redirect()->route('reset.password')->with('failed', 'Email Tidak Ditemukan');
        }

        $user->update([
            'status' => 'verify'
        ]);

        Auth::login($user);
        return redirect()->route('verify.password.otp');
    }

    public function resetPasswordUpdate(Request $request, $uniq_id){

        $request->validate([
            'password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:password',
        ]);

        $verify = Verification::where('user_id', Auth::user()->id)->orWhere('uniq_id', $uniq_id)
        ->orWhere('status', 'valid')->first();

        // dd($verify);

        if(!$verify){
            abort(404);
        }

        User::find($verify->user_id)->update([
            'password' => bcrypt($request->password),
            'status' => 'active'
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password Berhasil Diubah');
    }

    public function verifyOtp($uniq_id){
        
        $verify = Verification::where('user_id', Auth::user()->id)->orWhere('uniq_id', $uniq_id)
        ->orWhere('status', 'active')->count();

        // dd($verify);

        if(!$verify){
            abort(404);
        }

        return view('Auth.verifyPaasswod.show', compact('uniq_id'));
    }

    public function verifyOtpUpdate(Request $request,$uniq_id){
        
        $verify = Verification::where('user_id', Auth::user()->id)->orWhere('uniq_id', $uniq_id)
        ->orWhere('status', 'active')->first();

        // // dd($verify);

        if(!$verify){
            abort(404);
        }

        if(!md5($request->otp, $verify->otp)){
            $verify->update(([
                'status' => 'invalid'
            ]));
            return redirect('/reset-password')->with('otp', 'OTP Invalid');
        }

        $verify->update([
            'status' => 'valid'
        ]);

        return redirect()->route('reset.password.show', $uniq_id);
    }

    public function resetPasswordShow($uniq_id){
        
        $verify = Verification::where('user_id', Auth::user()->id)->orWhere('uniq_id', $uniq_id)
        ->orWhere('status', 'active')->count();

        // dd($verify);

        if(!$verify){
            abort(404);
        }

        return view('Auth.resetPasswordShow', compact('uniq_id'));
    }


    public function verifyPasswordOtp(Request $request){

        $type = 'reset_password';
      
        return view('Auth.verifyPaasswod.verify', compact('type'));
    }


    public function verifyPasswordOtpStore(Request $request){

        // dd($request->all());
         $type = $request->input('type');

        $user = User::where('email', Auth::user()->email)->first();

        // dd($type);
        
        if(!$user){
            return back()->with('failed_user', 'User Not Found');
        }

       $otp = rand(100000, 999999);

       $verify =  Verification::create(([
            'user_id' => $user->id,
            'uniq_id' => uniqid(),
            'otp' => md5($otp),
            'type' => $type,
            'send_via' => 'email'
        ]));
        // dd( $verify);
        Mail::to($user->email)->queue(new OtpEmail($otp));
        return redirect('/verify-otp/' . $verify->uniq_id);
        

    }
    
}
