<?php

namespace App\Http\Controllers;

use App\Mail\OtpEmail;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class VerifyController extends Controller
{
    public function index(Request $request){

        $type = 'register';
        $view = 'Verification.index';


        // if(session('type') === 'reset_password'){
        //     $type = 'reset_password';
        // }

        return view($view, compact('type'));
    }

    public function store(Request $request){

        // dd($request->all());

        $type = $request->input('type');


        if($request->input('type') === 'register'){

            $user = User::find($request->user()->id);
        } elseif ($type === 'reset_password') {
            // dd('stope here reset password');
            $user = User::where('email', $request->email)->first();
        }   

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


        // if($request->input('type') == 'reset_password'){

        //     dd('stope - here reset-password');

        //     return redirect('/verify-otp/' . $verify->uniq_id);
        // }
        // //alamat email type
        // if($request->input('type') === 'register'){

        //     //reset password masuk kesini? why?
        //     dd('stope - here register');
        //     return redirect('/verify/' . $verify->uniq_id);
        // }

        if ($type === 'reset_password'){

            return redirect('/verify-otp/' . $verify->uniq_id);
        }

        return redirect('/verify/' . $verify->uniq_id);

    }


    public function show($uniq_id){

        // dd(session('type'));
        
        $verify = Verification::where('user_id', Auth::user()->id)->orWhere('uniq_id', $uniq_id)
        ->orWhere('status', 'active')->count();

        // dd($verify);

        if(!$verify){
            abort(404);
        }

        return view('Verification.show', compact('uniq_id'));
    }

    public function update(Request $request,$uniq_id){
        
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
            return redirect('/verify')->with('otp', 'OTP Invalid');
        }

        $verify->update([
            'status' => 'valid'
        ]);
        
        if(session('type') === 'reset_password'){

            return redirect()->route('reset.password.show', $uniq_id);
        }

        User::find($verify->user_id)->update([
            'status' => 'active'
        ]);

        return redirect()->route('customer');


        // return view('Verification.show', compact('uniq_id'));
    }
}
