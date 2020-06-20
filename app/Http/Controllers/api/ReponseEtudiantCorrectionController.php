<?php


namespace App\Http\Controllers\api;
use App\Classe;
use App\Departement;
use App\Examen;
use App\Professeur;
use App\User;
use App\Solution;
use App\Choice;
use App\ReponseEtudiant;
use App\EtudiantExamen;
use App\Question;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;

class ReponseEtudiantCorrectionController extends Controller
{
    public function getReponseEtudiantOfExam(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token'],  'examen_id'=>['required','exists:examens,id'],
            'etudiant_id'=>['required','exists:etudiants,id']);
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return -1;}
        else{
            $reponseEtudiants=DB::table('reponse_etudiants')
                ->join('questions', 'reponse_etudiants.question_id', '=', 'questions.id')
                ->where('questions.examen_id',$request->input('examen_id'))
                ->where('reponse_etudiants.etudiant_id',$request->input('etudiant_id'))

                ->select('reponse_etudiants.id','reponse_etudiants.texte',
                    'reponse_etudiants.url', 'reponse_etudiants.choix',
                    'reponse_etudiants.note', 'reponse_etudiants.etudiant_id',
                    'reponse_etudiants.question_id')
                ->get();

            return Response::json($reponseEtudiants);

       }

}
    public function getNotesEtudiantOfExam(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token'],  'examen_id'=>['required','exists:examens,id'],
         );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return -1;}
        else{
            $notesEtudiants=DB::table('etudiant_examens')
                ->join('etudiants', 'etudiant_examens.etudiant_id', '=', 'etudiants.id')
                ->join('users', 'etudiants.user_id', '=', 'users.id')

                ->where('etudiant_examens.examen_id',$request->input('examen_id'))

                ->select('etudiant_examens.id','etudiants.id',
                    'etudiants.nom', 'etudiants.prenom',
                    'etudiant_examens.note', 'users.email',
                    'etudiant_examens.created_at')
                ->get();
            return Response::json($notesEtudiants);

        }

    }
    public function getCorrectionEtudiantOfExam(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return -2;}
        else{
            $api_token=$request['api_token'];
            $correction=$request['0'];
            $etudiant_id=$request['2'];
            $examen_id=$request['1'];
            $note_global=0;
            foreach ($correction as &$value) {
                $reponse_etudiant=ReponseEtudiant::where('id',$value['reponse_id'])->firstOrFail();
                $question=Question::where('id',$value['question_id'])->firstOrFail();
                if($question->note>= $reponse_etudiant['note']){
                $reponse_etudiant->note=$value['note'];
                $reponse_etudiant->update();
                $note_global+=$reponse_etudiant->note;
                }
                else {return -1;}
        }
            if(EtudiantExamen::where('examen_id',$examen_id)->where('etudiant_id',$etudiant_id)->exists()){
               $ee=EtudiantExamen::where('examen_id',$examen_id)->where('etudiant_id',$etudiant_id)->firstOrFail();
               $ee->note=$note_global;
               $ee->update();
                return 1;
            }
            else{
                $ee= new EtudiantExamen();
               $ee->examen_id=$examen_id; $ee->etudiant_id=$etudiant_id;
                $ee->note=$note_global;
                $ee->save();
                return 1;
            }
    }
       // return $ee;
    }

}
