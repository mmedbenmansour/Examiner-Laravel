<?php

namespace App\Http\Controllers\api;

use App\Classe;
use App\Etudiant;
use App\User;
use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;


class EtudiantController extends Controller
{

    public function createEtudiant(Request $request)
    {
        $etudiants = new Etudiant();

        $etudiants->nom = $request->input('nom');
        $etudiants->prenom = $request->input('prenom');
        $etudiants->user_id = $request->input('user_id');

        $etudiants->save();
        return response()->json($etudiants);
    }

    public function findEmailById($id)
    {
        $etudiant=User::find($id)->email;

        return response()->json($etudiant);
    }

    public function fetchEtudiants()
    {
        $etudiants = Etudiant::all();
        return response()->json($etudiants);
    }

    public function fetchEtudiantbyid(Request $request)
    {
        $user=  User::where('api_token',$request->input('api_token'))->first();

        $etudiants = Etudiant::where('user_id',$user->id)->first();
        return response()->json($etudiants);
    }

    public function updateEtudiant(Request $request)
    {
        $etudiants = Etudiant::find($request->input('id'));

        $etudiants->nom = $request->input('nom');
        $etudiants->prenom = $request->input('prenom');

        $etudiants->update();
        return response()->json($etudiants);
    }

    public function deleteEtudiant($id)
    {
        $etudiants = Etudiant::find($id);
        $etudiants->delete();

        return response()->json($etudiants);
    }

    //prend en parametre l'email de l'etudiant  et retourne l'objet etudiant
    public static function findByEmail(Request $request){
        $etudiant=Etudiant::with('user')->get()->where('user.email',htmlspecialchars($request->email))->first();
        return $etudiant;
    }

    //prend en parametre l'api token de l'etudiant  et retourne l'objet etudiant
    public static function findByApiToken($apiToken){
        $etudiant=Etudiant::with('user')->get()->where('user.api_token',htmlspecialchars($apiToken))->first();
        return $etudiant;
    }



}
