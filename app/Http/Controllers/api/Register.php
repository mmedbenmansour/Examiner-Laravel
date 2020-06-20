<?php


namespace App\Http\Controllers\api;
use App\Etudiant;
use \App\Http\Controllers\Controller;
use App\Professeur;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Request ;
use Mail;
use Illuminate\Support\Str;


class Register extends Controller
{
    public function cc( Request $request) {
        return $request;
    }
     public function validator(Request $request)
    {
        $rules=array(
            'email' => ['required','email', 'string',  'max:255','unique:users'],
              'password' => ['required', 'string', 'min:8' ],
        );
        $messages=array(
            'email.required' => 'entrez un mail valide',
            'password.required' => 'Please enter a short description.'
        );
        $validator=Validator::make($request->all(),$rules,$messages);
        if($validator->fails())
        {$messages=$validator->messages();
            $errors=$messages->all();
            return $errors;
        }
        else{
            $this->create($request);

            return 1;
        }


    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function create(Request $request)
    {
        $cc = substr(md5(uniqid(rand(), true)), 4, 4); // 16 characters long
$type="";
        $user =User::create([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
             'codeVerification'=> $cc,
            'api_token' => Str::random(60),
        ]);
if($request->input('type' )==1){
    $etudiant =Etudiant::create([
        'nom' => $request->input('nom'), 'prenom' =>  $request->input('prenom'), 'user_id'=> $user->id,]);
    $type="étudiant";
}
else {
    $professeur =Professeur::create([
        'nom' => $request->input('nom'), 'prenom' =>  $request->input('prenom'), 'user_id'=> $user->id,]);
    $type="professeur";

}

        $email = $request->email;
        Mail::send('template', array('cc' => $cc)  , function ($message) use ($cc,$email,$type) {

            $message->from('services.dev5@gmail.com', 'online exams');

            $message->to($email)->subject('Vérification de compte '.$type.' code de vérification :'.$cc);

        });

}



}
