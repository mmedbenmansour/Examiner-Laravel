<?php

namespace App\Http\Controllers\api;

use App\Classe;
use App\User;
use Illuminate\Http\Request;
use App\Notification;
use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
////////////////////////////////////////// Notification


    //public function createNotification(Request $request)
    public static function createNotification($type_notification,$user_id,$createur,$nom,$date,$reponse)
    {
        $notifications = new Notification();

        //$notifications->type_notification = $request->input('type_notification');
        //$notifications->user_id = $request->input('user_id');
        //$notifications->createur = $request->input('createur');
        $notifications->type_notification = $type_notification;
        $notifications->user_id = $user_id;
        $notifications->createur = $createur;


        if($notifications->type_notification == 'examen')
            $texte = 'L\'examen '. $nom .' de la classe '.$createur.' a été planifié le ' . $date;
        elseif($notifications->type_notification == 'classe')
            $texte = $createur . ' vous a ajouté a la classe ' .  $nom;
        elseif($notifications->type_notification == 'reclamation')
            $texte = 'La reponse que vous avez reçu pour la réclamation ' . $nom. ' est :' .$reponse;

        $notifications->texte = $texte;
        $notifications->save();
        //return response()->json($notifications);
    }

    public static function createNotificationForExamCreation($class_id,$nom,$date){
        $classe=Classe::with('etudiants')->where('id','=',$class_id)->first();
        if($classe!=null && $classe->etudiants!=null ){
            foreach ($classe->etudiants as $etudiant){
                $notifications = new Notification();
                $notifications->type_notification='examen';
                $notifications->user_id = $etudiant->user_id;
                $notifications->createur = $classe->nom;
                $notifications->texte = 'L\'examen '. $nom .' de la classe '.$classe->nom.' a été planifié le ' . $date;
                $notifications->save();
            }
        }
    }

    public function fetchNotifications()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }

    public function fetchNotificationbyid($id)
    {
        $notifications = Notification::find($id);
        return response()->json($notifications);
    }

    /*public function fetchNotificationbyUserid($user_id)
    {
        $notifications = Notification::find($user_id);
        return response()->json($notifications);
    }*/

    public function fetchNotificationbyUserid($user_id)
    {
        $apiToken = $user_id;
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $apiToken], $rules);
        if ($validator->fails()) {
            return Response::json(null);
        }
        $id=User::query()->where('api_token','=',$apiToken)->first()->id;
        $notifications = Notification::query()->where('user_id',$id)->orderBy('created_at','DESC')->get();
        //$notifications = App\Notification::with(['notifications' => function ($query) {$query->where('user_id', '=', $user_id);}])->get();
        //$notifications = Notification::with('notifications')->where('user_id','=',$user_id)->get();
        \Log::info(serialize($notifications));
        return response()->json($notifications);
    }

    public function updateNotification(Request $request)
    {
        $notifications = Notification::find($request->input('id'));
        $notifications->vu_a = $request->input('vu_a');

        $notifications->save();
        return response()->json($notifications);
    }

    public function deleteNotification(Request $request, $id)
    {
        $notifications = Notification::find($id);
        $notifications->delete();

        return response()->json($notifications);
    }

/////////////
}
