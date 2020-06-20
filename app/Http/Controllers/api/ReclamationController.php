<?php


namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Reclamation;
use App\User;

use DB;
use \App\Http\Controllers\Controller;

class ReclamationController extends Controller
{
////////////////////////////////////////// Reclamation
    public function createReclamation(Request $request)
    {
        $user=User::where('api_token',$request->input('api_token'))->first();

        $reclamations = new Reclamation();

        $reclamations->sujet = $request->input('sujet');
        $reclamations->contenu = $request->input('contenu');
        $reclamations->user_id = $user->id;
        $reclamations->reponse = "-";

        $query = $reclamations->save();

        return response()->json([$query, $reclamations]);
    }

    public function fetchReclamations()
    {
        $reclamations = DB::table('reclamations')
            ->join('users', 'reclamations.user_id', '=', 'users.id')
            ->select('reclamations.*', 'users.email')
            ->orderBy('created_at','DESC')
            ->get();
        if($reclamations)
            return $reclamations;
        else
            return [];
    }

    public function fetchReclamationsNonTraite()
    {


        $reclamations = DB::table('reclamations')
            ->join('users', 'reclamations.user_id', '=', 'users.id')
            ->select('reclamations.*', 'users.email')
            ->where('reclamations.reponse','LIKE',"%-%")
            ->orderBy('created_at','DESC')
            ->get();
       if($reclamations)
        return $reclamations;
        else
            return array();

    }

    public function fetchReclamationsTraite()
    {
        $reclamations = DB::table('reclamations')
            ->join('users', 'reclamations.user_id', '=', 'users.id')
            ->join('admins', 'reclamations.admin_id', '=', 'admins.id')
            ->select('reclamations.*', 'users.email','admins.nom')
            ->where('reclamations.reponse','NOT LIKE',"%-%")
            ->orderBy('created_at','DESC')
            ->get();
        if($reclamations)
            return $reclamations;
        else
            return array();
    }

    public function fetchReclamationbyid($id)
    {
        $reclamations = Reclamation::find($id);
        if($reclamations)
            return $reclamations;
        else
            return array();

    }

    public function updateReclamation(Request $request)
    {
        $reclamations = Reclamation::find($request->input('id'));

        $reclamations->reponse = $request->input('reponse');
        $reclamations->admin_id = $request->input('admin_id');

        $reclamations->update();
        return response()->json($reclamations);
    }

    public function deleteReclamation($id)
    {
        $reclamations = Reclamation::find($id);
        $reclamations->delete();

        return response()->json($reclamations);
    }

/////////////

}
