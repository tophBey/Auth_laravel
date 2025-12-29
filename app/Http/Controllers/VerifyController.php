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
    public function index(){
        return view('Verification.index');
    }

    public function store(Request $request){

        if($request->type === 'register'){

            $user = User::find($request->user()->id);
        }else{
            //reset password
        }

        if(!$user){
            return back()->with('failed_user', 'User Not Found');
        }

        $otp = rand(100000, 999999);

       $verify =  Verification::create(([
            'user_id' => $user->id,
            'uniq_id' => uniqid(),
            'otp' => md5($otp),
            'type' => $request->type,
            'send_via' => 'email'
        ]));

        Mail::to($user->email)->queue(new OtpEmail($otp));
        if($request->type === 'register'){
            return redirect('/verify/' . $verify->uniq_id);
        }
    }


    public function show($uniq_id){
        
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

        User::find($verify->user_id)->update([
            'status' => 'active'
        ]);

        return redirect()->route('customer');


        // return view('Verification.show', compact('uniq_id'));
    }
}
