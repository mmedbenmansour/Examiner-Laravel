<?php


namespace App\Http\Controllers\api;
use App\Classe;
use App\Departement;
use App\Examen;
use App\Professeur;
use App\User;
use App\Solution;
use App\Choice;

use App\Question;
use DB;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store(Request $request){
        $rules=array(
            'api_token'=> ['required','exists:users,api_token'],   'contenu'=> ['required'], 'examen_id'=>['required','exists:examens,id'], 'note'=>['required'],
            'numero'=>['required'], 'type_question'=>['required'], 'reponseDisponible'=>['required'],'reponse'=>['required'],
        );

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $question=new Question();
            $question->id=0 ;
            return $question;
        }
        else {
            return  $this->create($request);
        }
    }

    public function create (Request $request){
        $user=User::where('api_token',$request->input('api_token'))->first();
        $question=new Question();
        $question->numero=$request->input('numero');
        $question->contenu=$request->input('contenu');
        $question->note=$request->input('note');
        $question->type_question=$request->input('type_question');
        $question->examen_id=$request->input('examen_id');

            $question->save();

if($request->input('reponseDisponible')=="true"){
            $solution=new Solution();
    if($request->input('type_question')=='ouverte'){
        $solution->texte=$request->input('reponse');
        $solution->choix="";
        $solution->url="";

    }
    if($request->input('type_question')=='qcm'){
        $solution->texte="";
        $solution->choix= implode(",", $request->input('reponse'));
        $solution->url="";
        $this->creerLesChoix($request,$question);

    }
    if($request->input('type_question')=='fichier'){
        $solution->texte="";
        $solution->choix="";
        $solution->url=$request->input('reponse');

    }
    $solution->question_id=$question->id;
    $solution->save();



}

        $question->save();
        return Response::json($question);
    }
    public function creerLesChoix (Request $request,Question $question){
        $nbChoix=$request->input('nbReponse');
        for ($x = 1; $x <= $nbChoix; $x++) {
           $choix=new Choice();
           $choix->numero=$x;
           $choix->choice=$request->input('reponse'.$x);
           $choix->question_id=$question->id;
            $choix->save();
        }
    }
    public function findQuestionByExams(Request $request)
    {
        $rules=array('api_token'=> ['required','exists:users,api_token'],    'examen_id'=>['required','exists:examens,id']);
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return -1;}
        $questions=Question::where('examen_id',$request->input('examen_id'))->orderBy('numero')->get();;

        return Response::json($questions);
    }
    public function destroyQuestion(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token'],    'id'=>['required','exists:questions,id'],);

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return -1;}
        else{
        $question=Question::where('id',$request->input('id'))->first();
        $solution=Solution::where('question_id',$request->input('id'))->first();
        if($solution) {$solution->delete();}

        if($question->type_question=="qcm") {
        $choix=Choice::where('question_id',$request->input('id'))->first();
        $choix->delete();
        }
        if($question){
            $question->delete();
            $state=true;
            return Response::json(['etat' => $state]);
        }
    }  }


    public function getQuestionDetails(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token'],    'id'=>['required','exists:questions,id'],);

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return -1;}
        else{
            $question=Question::where('id',$request->input('id'))->first();
            $solution=Solution::where('question_id',$request->input('id'))->first();


            if($question->type_question=="qcm") {
                $choix=Choice::where('question_id',$request->input('id'))->get();

            }
            else $choix=null;

                return Response::json(['solution' =>$solution,'choix' =>$choix]);

        }  }
///PArtie 2 ajouté le 19/06/2020
//prend en parametre l'id de la question et retourne l'objet question
    public static function findById($questionId){
        return Question::query()->where('id','=',$questionId)->first();
    }

    //prend en parametre l'id de l'examen et retourne un tableau des id des questions de cet examen
    public static function getIdsByExamId($examId){
        $db=DB::table('questions')->select('id')->where('examen_id','=',$examId)->get();
        $result=array();
        foreach ($db as $value){
            array_push($result,$value->id);
        }
        return $result;
    }
}
