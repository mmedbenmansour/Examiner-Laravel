<?php


namespace App\Http\Controllers\api;
use App\Etudiant;
use \App\Http\Controllers\Controller;
use App\Professeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\User;
use Carbon\Carbon;


class Login extends Controller
{
    public function validator(Request $request)
    {
        $rules=array(
            'email' => ['required','email', 'string',  'max:255'],
            'password' => ['required', 'string', 'min:8' ],
        );
        $messages=array(
            'email.required' => 'email erroné',
            'password.required' => 'mot de passe erroné'
        );
        $validator=Validator::make($request->all(),$rules,$messages);
        if($validator->fails())
        {$messages=$validator->messages();
            $errors=$messages->all();
            return $errors;
        }
        else{
           return $this->testUser($request);
        }}


    public function verifiyAccount(Request $request)
    {
        $user=  User::where('api_token', $request->input('api_token'))->
            where('codeVerification', $request->input('codeVerification'))->first();
        if($user) {
            $user->email_verified_at = Carbon::now();
            $user->save();
            return json_encode(array(1));
        }
            else return json_encode(array(-1));




    }



    public function testUser(Request $request){
     $user=  User::where('email', $request->input('email'))->first();
        if($user!=null) {
            if (Hash::check($request->input('password'), $user->password)) {
                if ($user->email_verified_at != null) {
                    $prof=  Professeur::where('user_id', $user->id)->first();
                    $etud=  Etudiant::where('user_id', $user->id)->first();
                    if($prof){
                        return json_encode(array(1,$user->api_token,"professeur",$prof->id ));
                    }
                    else if ( $etud){
                        return json_encode(array(1,$user->api_token,"etudiant",$etud->id ));
                    }
                }
                else  return json_encode(array(-2,$user->api_token ));
            }
            else return json_encode(array(-3));
        }
            else { return  json_encode(array(-3));}


        }
    public function testUserToken(Request $request){
        $user=  User::where('api_token', $request->input('api_token'))->first();
        if($user!=null) {
                 return json_encode(array(2,$user->email));
        }
        else {  return json_encode(array(-1));}


    }


}
