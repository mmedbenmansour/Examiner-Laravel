<?php


namespace App\Http\Controllers\api;

use App\Solution;
use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;

class SolutionController extends Controller
{
    //retourne la solution d'une question en prenant en parametre l'id de la question
    public static function findSolutionByIdQuestionId($questionId){
        return Solution::query()->where('question_id','=',$questionId)->first();
    }

    //retourne l'attribut choix (qcm) de l'objet solution  d'une question en prenant en parametre l'id de la question
    public static function getQCMSolutionByQuestionId($questionId){
        return self::findSolutionByIdQuestionId($questionId)->choix;
    }
}
