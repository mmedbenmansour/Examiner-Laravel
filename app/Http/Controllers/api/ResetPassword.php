<?php


namespace App\Http\Controllers\api;


use App\Etudiant;
use App\Professeur;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Mail;
class ResetPassword extends \App\Http\Controllers\Controller
{

    public function emailVerifyForReset(Request $request){

        $user=  User::where('email', $request->input('email'))->first();
        if($user) {
                $cc=substr(md5(uniqid(rand(), true)), 4, 4);
                $user->codeVerification =$cc;
                $user->save();
                $email = $request->input('email');
                Mail::send('resetPassword', array('cc' => $user->codeVerification)  , function ($message) use ($cc,$email) {
                $message->from('services.dev5@gmail.com', 'online exams');
                $message->to($email)->subject('Vérification de compte : code de vérification :'.$cc);
            });

            return json_encode(array(1));
        }
        else return json_encode(array(-1));
    }
    public function codeVerifyForReset(Request $request){

        $user=  User::where('email', $request->input('email'))->where('codeVerification', $request->input('codeVerification'))->first();
        if($user) {
            return json_encode(array(1));
        }
        else return json_encode(array(-1));
    }
    public function passwordVerifyForReset(Request $request){

        $user=  User::where('email', $request->input('email'))->where('codeVerification', $request->input('codeVerification'))->first();
        if($user) {
            $rules=array('password' => ['required', 'string', 'min:8' ],);
            $messages=array('password.required' => 'entrer un mot de passe valide.');
            $validator=Validator::make($request->all(),$rules,$messages);
            if($validator->fails())
            {   $messages=$validator->messages();
                $errors=$messages->all();
                return json_encode(array(-3,$errors));
            }
            else if(Hash::check(request('password'), $user->password))
            {
                return json_encode(array(-3,"Ce mot de passe est déja utilisée"));
            }
            else
            {
                $user->password= Hash::make($request->input('password'));
                $user->save();
                return json_encode(array(1,"Changement est validé veuillez se connecter"));
            }

        }
        else return json_encode(array(-3,"Erreur des données "));
    }


}
