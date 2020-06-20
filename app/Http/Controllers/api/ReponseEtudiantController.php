<?php


namespace App\Http\Controllers\api;
use App\Choice;
use App\Classe;
use App\Etudiant;
use App\Examen;
use App\Http\Controllers\QuestionReponse;
use App\Question;
use App\ReponseEtudiant;
use Carbon\Carbon;
use Facade\Ignition\QueryRecorder\Query;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Array_;
use \App\Http\Controllers\Controller;

class ReponseEtudiantController extends Controller
{
// il enregistre l'ensemble des reponses significatives dans la BDD et renvoie un message.
    public function getAnswers(Request $request)
    {
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $request->input('api_token')], $rules);
        if ($validator->fails()) {
            return Response::json([-2, ""]);
        }
        if ($request == null) {
            return Response::json([-2, "Erreur requettes, merci de nous envoyer une reclamation. "]);

        } elseif ($this->isValidSubmitTime($request->all()[0]['question_id']) == false) {
            return Response::json([-3, "Vous avez depasser la durée d'examen. aucune réponse n'a été enregistrée"]);
        } else {
            $etudiant = EtudiantController::findByApiToken($request->input('api_token'));
            if ($this->hasAlreadyAnswered($request->input('examen_id'), $etudiant->id)) {
                return Response::json([-4, "Vous avez déjà répondu aux questions de l'examen."]);
            }
            foreach ($request->all() as $element) {
                if ($element != $request->input('api_token') && $element != $request->input('examen_id')) {
                    $this->store($element, $etudiant->id);
                }
            }
            ExamenController::correctQCMExam($request->input('examen_id'), $etudiant->id);
        }
        return Response::json([2, "Vos réponses ont été enregistrées. "]);
    }

    //prend en parametre l'id de l'examen et l'id de l'etudiant
    // verifie si l'etudiant a déja répondu aux question de l'examen si oui elle retourne la valeur true , sinon false.
    private function hasAlreadyAnswered($examenId, $etudiantId)
    {
        $questionIds = QuestionCOntroller::getIdsByExamId($examenId);
        $nb = ReponseEtudiant::query()->where('etudiant_id', '=', $etudiantId)->whereIn('question_id', $questionIds)->count();
        if ($nb > 0) {
            return true;
        } else {
            return false;
        }

    }

    // prend en parametre la reponse d'une question et l'id de l'etudiant
    // traite et enregistre la reponse selon le type de la question
    private function store($reponseEtudiant, $etudiant_id)
    {
        $reponse = new ReponseEtudiant();
        if ($reponseEtudiant == null) return -1;
        if ($reponseEtudiant['texte'] != null && $reponseEtudiant['texte'] != '') {
            $reponse->texte = $reponseEtudiant['texte'];
            $reponse->url = '';
            $reponse->choix = '';
            $reponse->note = 0;
            $reponse->etudiant_id = $etudiant_id;
            $reponse->question_id = $reponseEtudiant['question_id'];
            $reponse->save();
        } elseif ($reponseEtudiant['url'] != null && $reponseEtudiant['url'] != '') {
            return 0;
            $reponse->texte = '';
            $reponse->url = '';
            $reponse->choix = '';
            $reponse->note = 0;
            $reponse->etudiant_id = $etudiant_id;
            $reponse->question_id = $reponseEtudiant['question_id'];
            $reponse->save();

        } elseif (count($reponseEtudiant['choices']) != 0) {
            $reponse->choix = $this->getChoiceAnswer($reponseEtudiant['choices'], $reponseEtudiant['question_id']);
            if ($reponse->choix == null || strlen($reponse->choix) == 0) return -1;
            $reponse->url = '';
            $reponse->texte = '';
            $reponse->note = $this->correctQCMQuestion($reponse->choix, $reponseEtudiant['question_id']);
            $reponse->etudiant_id = $etudiant_id;
            $reponse->question_id = $reponseEtudiant['question_id'];
            $reponse->save();
            $this->correctQCMQuestion($reponse, $reponseEtudiant['question_id']);
        }
    }

    //corrige les reponses aux question QCM , prend en parqametre la reponse et l'id de la question. elle verifie
    // l'egalité de la reponse avec la solution proposée, si elles sont eqaux
    //Si oui elle donne la note de la question à a reponse, sinon 0
    private function correctQCMQuestion($reponse, $idQuestion)
    {
        $note = 0;
        $solution = SolutionController::getQCMSolutionByQuestionId($idQuestion);
        $question = QuestionCOntroller::findById($idQuestion);
        if ($reponse == $solution) {
            $note = $question->note;
        }
        return $note;
    }

    // prend en parametre l'id de la question et la reponse de l'etudiant,
    // analyse la reponse à la question qcm et
    // retourne une chaine contenant les numeros des choix selectionnés
    private function getChoiceAnswer($reponseEtudiant, $questionId)
    {
        $choices = Choice::query()->where('question_id', "=", $questionId)->orderBy('numero', 'ASC')->get();

        if ($choices == null || count($choices) == 0) return null;
        else {
            $result = "";
            for ($i = 0; $i < (count($reponseEtudiant)); $i++) {
                if ($reponseEtudiant[$i] == true) {
                    $result .= $choices[$i]->numero . ",";
                }
            }
            if (substr($result, -1) == ",") {
                $result = substr($result, 0, -1);
            }
            return $result;
        }

    }

    //verifie si le moment ou la date de reception des reponses est valide.
    private function isValidSubmitTime($id)
    {
        $examen = $this->getExamByQuestionID($id);
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

    private function getExamByQuestionID($y)
    {
        return Question::with('examen')->find($y)->examen;
    }


    //prend en parametre l'id de l'etudiant et retourne les id des question auqels il a répondu
    public function getAnsweredQuestionIds($id)
    {
        $result = ReponseEtudiant::query()->where('etudiant_id', $id)->get();
        $questionIds = array();
        foreach ($result as $element) {
            array_push($questionIds, $element->question_id);
        }
        return $questionIds;
    }

    //prend en parametre l'id d el'etudiant et retourne les id des examen auquel l'etudiant a déja participé
    public function findPassedExamByStudent($id)
    {
        $result = Examen::wherehas('questions', function ($q) use ($id) {
            $q->whereIn('id', $this->getAnsweredQuestionIds($id));
        })->get();
        $examensIds = array();
        foreach ($result as $element) {
            array_push($examensIds, $element->id);
        }
        return $examensIds;
    }

    //prend en parametre l'id de l'etudiant et retourne les classes et les examens auquel il a deja participé.
    public function findClassesWithPassedExams($id){
        $result = Classe::with(['examens' => function ($q) use ($id) {
            $q->whereIn('id', $this->findPassedExamByStudent($id));
        }])
            ->wherehas('examens', function ($q) use ($id) {
                $q->whereIn('id', $this->findPassedExamByStudent($id));
            })->get();
        return $result;
    }

    //prend en parametre l'api token de l'etudiant , retourne les classes et les examen auquel il a participé
    public function getClassesOfPassedExams(Request $request){
        $apiToken = $request->input('api_token');
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $apiToken], $rules);
        if ($validator->fails()) {
            return Response::json([-2, ""]);
        }
        $etudiant = EtudiantController::findByApiToken($apiToken);
        $result = $this->findClassesWithPassedExams($etudiant->id);
        return Response::json([1, $result]);
    }

    //prend en parametre l'api token de l'etudiant et l'id de l'examen
    // retourne les questions de l'examen et les reponse de l'etudiant sur ces questions
    public function getReponsesOfStudent(Request $request)
    {
        $apiToken = $request->input('api_token');
        $rules = array('api_token' => ['required', 'exists:users,api_token']);
        $validator = Validator::make(['api_token' => $apiToken], $rules);
        if ($validator->fails()) {
            return Response::json([-1, ""]);
        }
        $etudiantid = EtudiantController::findByApiToken($apiToken)->id;
        $examId = $request->input('eid');
        $bd = Question::
        with(['reponseEtudiants' => function ($q) use ($etudiantid) {
            $q->where('etudiant_id', '=', $etudiantid);
        }])
            ->where('examen_id', '=', $examId)->orderBy('numero', 'Asc')->get();

        $result = array();
        foreach ($bd as $element) {
            $questionReponse = $this->formatQuestionReponse($element);
            array_push($result, $questionReponse);
        }

        return Response::json([1, $result]);
    }

    //retourne un objet question reponse contenant le numero de la question, la question et la reponse de l'etudiant
    public function formatQuestionReponse($questionReponse)
    {
        $reponse = '';
        if ($questionReponse->reponseEtudiants->first() == null) {
            $reponse = 'aucune réponse';
        } elseif ($questionReponse->type_question == 'ouverte') {
            $reponse = $questionReponse->reponseEtudiants->first()->texte;
        } elseif ($questionReponse->type_question == 'qcm') {
            $reponse = $this->getQCMAnswerByNumero($questionReponse->reponseEtudiants->first()->choix, $questionReponse->id);
        } elseif ($questionReponse->type_question == 'fichier') {
            //a modifier lors de l'implementation des questions de type fichier
            $reponse = 'aucune réponse';
        }
        $x = new QuestionReponse();
        $x->numero = $questionReponse->numero;
        $x->question = $questionReponse->contenu;
        $x->reponse = $reponse;
        return $x;
    }

    //prend en parametre les numero des choix qcm et l'id de la question , retourne une chaine contenant le texte des choix de l'etudiant
    public function getQCMAnswerByNumero($nums, $id)
    {
        $answer = '';
        $x = explode(',', $nums);

        $db = DB::table('choices')
            ->join('questions', 'questions.id', '=', 'choices.question_id')
            ->where('questions.id', '=', $id)
            ->whereIn('choices.numero', $x)
            ->get('choices.choice');
        foreach ($db as $element) {
            $answer .= $element->choice . "\n";
        }
        return $answer;
    }
}
