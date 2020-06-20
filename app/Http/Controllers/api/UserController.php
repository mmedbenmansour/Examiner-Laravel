<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Hash;
use App\User;
use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        $user = new User();
        $user->email = $request->email;
        $password = Hash::make($request->password);
        $user->password = $password;
        $user->codeVerification = $request->codeVerification;

        $user->save();
        //return response()->json($request->all());
        return response()->json($user);
    }

    public function changePassword(Request $request){

        if(!(Hash::check($request->oldPassword, User::find($request->id)->password))){
            $resp =false;
        }else{
            $resp = User::find($request->id)->update(['password'=> Hash::make($request->newPassword)]);
        }
        return response()->json($resp);
    }

}
