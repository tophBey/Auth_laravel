<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;

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
            'uniqe_id' => uniqid(),
            'otp' => md5($otp),
            'type' => $request->type,
            'send_via' => 'email'
        ]));

        


    }
}
