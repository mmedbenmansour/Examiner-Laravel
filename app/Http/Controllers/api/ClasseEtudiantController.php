<?php

namespace App\Http\Controllers\api;

use App\Classe;
use App\ClasseEtudiant;
use App\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use \App\Http\Controllers\Controller;

class ClasseEtudiantController extends Controller
{


    public function affecterAClasse(Request $request)
    {   $res=0;
        $classe = Classe::find($request->id_classe);
        $etudiant = EtudiantController::findByEmail($request);
        $professeur=ProfesseurController::findByApiToken($request->api_token);
        if($etudiant){
            $estAffecter = $this->estAffecter($classe, $etudiant);

            if ($estAffecter == false) {
                $classeEtudiant = new ClasseEtudiant();
                $classeEtudiant->classe_id = $classe->id;
                $classeEtudiant->etudiant_id = $etudiant->id;
                $classeEtudiant->save();
                NotificationController::createNotification('classe',$etudiant->id,'Pr. '.$professeur->nom.' '.$professeur->prenom,$classe->nom,now()->toString(),null);
                $res=1;
            }else{
                $res=-2;
            }
        }else{
            $res=-1;
        }
        $classe = Classe::with('etudiants')->where('id',$request->id_classe)->first();
        return Response::json([$res,$classe]);
    }

    public function estAffecter($classe, $etudiant)
    {
        if ($classe) {
            if ($etudiant) {
                $nbParticipation = ClasseEtudiant::all()->where('classe_id',$classe->id)->where('etudiant_id',$etudiant->id)->count();
                if ($nbParticipation == 0) {
                    return false;
                } else {
                    return true;
                }
                return null;
            }
            return null;
        }
    }
}
