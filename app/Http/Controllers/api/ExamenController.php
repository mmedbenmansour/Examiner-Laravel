<?php
namespace App\Http\Controllers\api;

use App\Classe;
use Illuminate\Http\Request;
use App\User;
use App\Examen;
use App\Etudiant;
use Illuminate\Support\Facades\Response;
use \App\Http\Controllers\Controller;
use App\Professeur;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Quotation;
use App\EtudiantExamen;
use App\Question;
use App\ReponseEtudiant;
use Carbon\Carbon;

class ExamenController extends Controller
{

public function store(Request $request){
        $rules=array(
            'api_token'=> ['required','exists:users,api_token'],   'nom'=> ['required'], 'classe_id'=>['required','exists:classes,id'], 'bareme'=>['required'],
                'seuil_reussite'=>['required'], 'duree'=>['required'], 'date_examen'=>['required'],
        );

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $examen=new Examen();
            $examen->id=0 ;
            return $examen;
        }
        else {
          return  $this->create($request);
        }
}

public function create (Request $request){
$user=User::where('api_token',$request->input('api_token'))->first();
$professeur=Professeur::with('user')->where('user_id',$user->id)->firstOrFail();
    $classe=Classe::where('id',$request->input('classe_id'))->first();
    $examen=new Examen();
    $examen->nom=$request->nom;
    $examen->classe_id=$request->classe_id;
    $examen->bareme=$request->bareme;
    $examen->seuil_reussite=$request->seuil_reussite;
    $examen->bareme=$request->bareme;
    $examen->duree=$request->duree;
    $examen->date_examen=$request->date_examen;
    $examen->save();
    NotificationController::createNotificationForExamCreation($classe->id,$classe->nom,$request->date_examen);
return Response::json($examen);
}

public function findExamsByClasse(Request $request)
{
    $rules=array('api_token'=> ['required','exists:users,api_token'],    'classe_id'=>['required','exists:classes,id']);
    $validator=Validator::make($request->all(),$rules);
    if($validator->fails())
    {return -1;}
$exams=Examen::where('classe_id',$request->input('classe_id'))->get();;

return Response::json($exams);
}
    public function findAllExams(Request $request)
    {
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {return null;}
        $user=User::where('api_token',$request->input('api_token'))->first();
        $professeur=Professeur::with('user')->where('user_id',$user->id)->firstOrFail();

        $exams=DB::table('examens')
            ->join('classes', 'classes.id', '=', 'examens.classe_id')
            ->join('professeurs', 'classes.professeur_id', '=','professeurs.id' )
            ->where('professeurs.id',$professeur->id)
            ->select('examens.id','examens.nom', 'examens.classe_id', 'examens.bareme',
                'examens.seuil_reussite', 'examens.duree', 'examens.date_examen')
            ->get();

        return Response::json($exams);
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
/////////PARTIE 2///////////////////::: Ajouté le 19/06/2020
//pren en parametre l 'api token d'un etudiant et retourne une liste des examens futurs et actuels dont l'etudiant n'a pas encore répondu.
    public function findFutureExamByEtudiant(Request $request)
    {
        $id=$request->input('api_token');
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make(['api_token'=>$id],$rules);
        if($validator->fails()) {return Response::json([-1, null]);}

        $etudiant=EtudiantController::findByApiToken($id);
        $db = Etudiant::with(['classes.examens'])->where('id', $etudiant->id)->first();

        foreach ($db->classes as $c) {
            foreach ($c->examens as $key => $e) {
                if (Carbon::createFromFormat("Y-m-d H:i:s", $e->date_examen)->setSecond($e->duree * 3600) < Carbon::now()) {
                    unset($c->examens[$key]);
                }
                if ($this->isStudentAnsweredToExam($etudiant->id,$e->id)){
                    unset($c->examens[$key]);
                }
            }
        }
        return Response::json([1, $db]);

    }

    // prend en parametre l'id de l'etudiant et l'id de l'examen , elle retourne true si l'etudiant a deja répondu a repondu au moins sur une question sinon false
    private static function isStudentAnsweredToExam($etudiantid,$examenid){
        $x=DB::table('reponse_etudiants')
            ->join('questions','reponse_etudiants.question_id','=','questions.id')
            ->join('examens','examens.id','=','questions.examen_id')
            ->where('examens.id','=',$examenid)
            ->where('reponse_etudiants.etudiant_id','=',$etudiantid)
            ->count('*');
        if ($x>0){
            return true;
        }else{
            return false;
        }
    }

    //prend en parametre l'id de l'examen et l'api token de l'utidiant , elle retourne l'examen avec ses questions
    public function loadExam(Request $request)
    {
        $apiToken = $request->input('api_token');
        $id=$request->input('id');
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $apiToken], $rules);
        if ($validator->fails()) {
            return Response::json([-2, ""]);
        }

        $db = Examen::with(['questions' => function ($q) {
            $q->orderBy('numero', 'Asc');
        }, 'questions.choices' => function ($q) {
            $q->orderBy('numero', 'Asc');
        }])->where('id', $id)->firstOrFail();
        if ($this->isValidTime($db))
            return Response::json([1, $db]);
        else{
            return Response::json([-1, "vous ne pouvez plus repondre aux question de cet examen. "]);
        }
    }

    //prend en parametre un objet examen et retourne true si la date actuelle est inferieur a la date de fain d'examen et superieur a la date de debut de l'examen sion elle retourne false
    private function isValidTime($examen)
    {
        $dateExamen = $examen->date_examen;
        $duree = $examen->duree;
        $dateDebutExamen = Carbon::createFromFormat("Y-m-d H:i:s", $dateExamen);
        $dateFinExamen = Carbon::createFromFormat("Y-m-d H:i:s", $dateExamen)->setSecond($duree * 3600);
        $now = Carbon::now();
        if ($now < $dateDebutExamen || $now > $dateFinExamen) {
            return false;
        } else {
            return true;
        }
    }

    //prend en parametre l'api token de l'etudiant et retourne l'examen corrigé avec sa note pour un etudiant
    public function findExamsWithNote(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make(['api_token'=>$request->input('api_token')],$rules);
        if($validator->fails()) {return Response::json([-1, null]);}
        $etudiant=EtudiantController::findByApiToken($request->input('api_token'));
        $db=EtudiantExamen::with('examen')->where('etudiant_id','=',$etudiant->id)->get();
        return Response::json([1, $db]);
    }


    //pren en parametre l'id de l'examen et l'id de l'etudiant , corrige l'examen QCM et enregistre la note globale dans la BD
    public static function correctQCMExam($examenId,$etudiantId){
        if (self::isAllQuestionAreQCM($examenId)){
            $questionsIDs=QuestionCOntroller::getIdsByExamId($examenId);
            $noteGlobale=self::calculNoteGlobaleExam($questionsIDs,$etudiantId);
            $noteExamen= new EtudiantExamen();
            $noteExamen->note=$noteGlobale;
            $noteExamen->etudiant_id=$etudiantId;
            $noteExamen->examen_id=$examenId;
            $noteExamen->save();
            return 1;
        }else{
            return 0;
        }
    }

    //prend en parametre l'id de l'examen et verifie si tous ses question sont de type qcm
    private static function isAllQuestionAreQCM($examenId){
        return (self::getNumberOfQuestionQCMByExam($examenId)==self::getNumberOfQuestionByExam($examenId));
    }

    //prend en parametre l'id de l'examen et retoune le nombre des question qcm de l'examen
    private static function getNumberOfQuestionQCMByExam($examenId){
        $nb=Question::query()->where('examen_id','=',$examenId)->where('type_question','=','qcm')->count('*');
        return $nb;
    }

    //prend en parametre l'id de l'examen et retoune le nombre des question de l'examen (tout type confondu)
    private static function getNumberOfQuestionByExam($examenId){
        $nb=Question::query()->where('examen_id','=',$examenId)->count('*');
        return $nb;
    }

    //prend en parametre l'id de la question et l'id de l'etudiant , retourne la note global de l'examen.
    private static function calculNoteGlobaleExam($questionIds,$etudiantId){
        $db=DB::table('reponse_etudiants')->where('etudiant_id','=',$etudiantId)->whereIn('question_id',$questionIds)->sum('note');
        return $db;
    }



    public function getNbExamsByClassOfProfessor(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make(['api_token'=>$request->input('api_token')],$rules);
        if($validator->fails()) {return Response::json([-1, null]);}
        $id=ProfesseurController::findByApiToken($request->input('api_token'))->id;
        $db=DB::table('classes')
            ->selectRaw('classes.nom, COUNT(examens.id) AS nbexams')
            ->leftJoin('examens','examens.classe_id','=','classes.id')
            ->where('professeur_id','=',$id)
            ->groupBy(array('classes.nom'))
            ->get();
        $classNames=array();
        $nbExams=array();
        foreach ($db as $element){
            array_push($classNames,$element->nom);
            array_push($nbExams,$element->nbexams);
        }
        return Response::json([1,$classNames,$nbExams]);
    }


    public function getnoteByExamOfStudent(Request $request){
        $rules=array('api_token'=> ['required','exists:users,api_token']);
        $validator=Validator::make(['api_token'=>$request->input('api_token')],$rules);
        if($validator->fails()) {return Response::json([-1, null]);}
        $id=EtudiantController::findByApiToken($request->input('api_token'))->id;
        $db=DB::table('examens')
            ->selectRaw('examens.nom, etudiant_examens.note AS nbexams')
            ->leftJoin('etudiant_examens','etudiant_examens.examen_id','=','examens.id')
            ->where('etudiant_examens.etudiant_id','=',$id)
            ->orderBy('examens.date_examen','ASC')
            ->get();
        $examNames=array();
        $noteExams=array();
        foreach ($db as $element){
            array_push($examNames,$element->nom);
            array_push($noteExams,$element->nbexams);
        }
        return Response::json([1,$examNames,$noteExams]);
    }




}

