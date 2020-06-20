<?php

namespace App\Http\Controllers\api;

use App\User;
use Illuminate\Http\Request;
use App\Professeur;
use \App\Http\Controllers\Controller;
use DB;
class ProfesseurController extends Controller
{
////////////////////////////////////////// Professeur
    public function createProfesseur(Request $request)
    {
        $professeurs = new Professeur();
        $professeurs->nom = $request->input('nom');
        $professeurs->prenom = $request->input('prenom');

        $professeurs->save();
        return response()->json($professeurs);
    }

    public function findEmailById($id){
        $professeur=User::find($id)->email;
        return response()->json($professeur);
    }

    public function fetchProfesseurs()
    {
        $professeurs = DB::table('professeurs')
            ->join('users', 'professeurs.user_id', '=', 'users.id')
            ->select('professeurs.*', 'users.email')
            ->orderBy('created_at','DESC')
            ->get();
        return response()->json($professeurs);
    }

    public function fetchProfesseurbyid(Request $request)
    {
        $professeurs = ProfesseurController::findByApiToken($request->input('api_token'));
        return response()->json($professeurs);
    }


    public function updateProfesseur(Request $request)
    {
        $professeurs = Professeur::find($request->input('id'));

        $professeurs->nom = $request->input('nom');
        $professeurs->prenom = $request->input('prenom');

        $professeurs->save();
        return response()->json($professeurs);
    }

    public function deleteProfesseur($id)
    {
        $professeurs = Professeur::find($id);
        $professeurs->delete();

        return response()->json($professeurs);
    }

    public static function findByApiToken($apiToken){
        $professeur=Professeur::with('user')->get()->where('user.api_token',htmlspecialchars($apiToken))->first();
        return $professeur;
    }

}
