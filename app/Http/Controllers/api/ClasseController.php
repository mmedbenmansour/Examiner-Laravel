<?php

namespace App\Http\Controllers\api;

use App\Classe;
use App\Departement;
use App\Professeur;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use \App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DB;
class ClasseController extends Controller
{

    public function create(){
        //afficher la page de creation d'une classe
    }

    public function store (Request $request){
         $user=User::where('api_token',$request->input('api_token'))->first();
        $professeur=Professeur::with('user')->where('user_id',$user->id)->firstOrFail();

            $classe=new Classe();
        $classe->nom=$request->nom;
      $classe->professeur_id=$professeur->id;
      $classe->save();
        return Response::json($classe);
    }

    public function findByProf(Request $request)
    {
        $user=User::where('api_token',$request->input('api_token'))->first();
        $professeur=Professeur::with('user')->where('user_id',$user->id)->firstOrFail();
        $classes = Classe::with('etudiants')->where('professeur_id',$professeur->id)->get();

        return Response::json($classes);
    }

    public function destroy (Request $request){
        $user=User::where('api_token',$request->input('api_token'))->first();
        $professeur=Professeur::with('user')->where('user_id',$user->id)->firstOrFail();
        $classe = Classe::with('etudiants')->where('professeur_id',$professeur->id)->where('id',$request->input('id'))->first();
        if($classe){
            $classe->delete();
            $state=true;
            return Response::json(['etat' => $state]);
        }
    }



    public function findbyProfApiToken(Request $request){
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $request->input('api_token')], $rules);
        if ($validator->fails()) {
            return Response::json([-2, ""]);
        }else{
            $id=ProfesseurController::findByApiToken($request->input('api_token'))->id;
            $classes= Classe::query()->where('professeur_id','=',$id)->get(array('id','nom'));
            return Response::json([1, $classes]);
        }
    }


    public function getMeansMarksByExam(Request $request){
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $request->input('api_token')], $rules);
        if ($validator->fails()) {
            return Response::json([-2, ""]);
        }else{
            $idClass=$request->input('class_id');
            $db=DB::table('examens')
                ->selectRaw('examens.nom, AVG(etudiant_examens.note) as mean')
                ->join('etudiant_examens','examens.id','=','etudiant_examens.examen_id')
                ->where('classe_id','=',$idClass)
                ->groupBy(array('examens.nom'))
                ->get();
            $examName=array();
            $means=array();
            foreach ($db as $element){
                array_push($examName,$element->nom);
                array_push($means,round($element->mean,2));
            }
            return Response::json([1,$examName,$means]);
        }
    }

    public function getNbStudentsByClassOfProfessor(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make(['api_token'=>$request->input('api_token')],$rules);
        if($validator->fails()) {return Response::json([-1, null]);}
        $id=ProfesseurController::findByApiToken($request->input('api_token'))->id;
        $db=DB::table('classes')
            ->selectRaw('classes.nom, COUNT(classe_etudiant.id) AS nbstd')
            ->leftJoin('classe_etudiant','classe_etudiant.classe_id','=','classes.id')
            ->where('professeur_id','=',$id)
            ->groupBy(array('classes.nom'))
            ->get();
        $classNames=array();
        $nbStudents=array();
        foreach ($db as $element){
            array_push($classNames,$element->nom);
            array_push($nbStudents,$element->nbstd);
        }
        return Response::json([1,$classNames,$nbStudents]);
    }


}
