<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
return $request->user();

});
*/
Route::middleware('auth:api')->get('/foo', function () {
    return 'Hello World';
});

Route::post('register', 'api\Register@validator', ['middleware' => 'cors', function(){return;}]);
Route::post('test2', 'api\Register@test', ['middleware' => 'cors', function(){return;}]);

Route::post('login', 'api\Login@validator', ['middleware' => 'cors', function(){return;}]);
Route::post('testUserToken', 'api\Login@testUserToken', ['middleware' => 'cors', function(){return;}]);
Route::post('verifiyAccount', 'api\Login@verifiyAccount', ['middleware' => 'cors', function(){return;}]);


Route::post('emailVerifyForReset', 'api\ResetPassword@emailVerifyForReset', ['middleware' => 'cors', function(){return;}]);
Route::post('codeVerifyForReset',  'api\ResetPassword@codeVerifyForReset', ['middleware' => 'cors', function(){return;}]);
Route::post('passwordVerifyForReset',  'api\ResetPassword@passwordVerifyForReset', ['middleware' => 'cors', function(){return;}]);


////////////////////

Route::get('professor/classes','api\ClasseController@findByProf');
Route::post('professor/classes','api\ClasseController@store');
Route::delete('professor/classes','api\ClasseController@destroy');
Route::post('professor/affect','api\ClasseEtudiantController@affecterAClasse');
/////////////////////////////
Route::post('professor/examen','api\ExamenController@store');
Route::get('professor/findExamsByClasse','api\ExamenController@findExamsByClasse');
Route::get('professor/findAllExams','api\ExamenController@findAllExams');

/////////////////////////////
Route::post('professor/createQuestion','api\QuestionController@store');
Route::get('professor/getQuestions','api\QuestionController@findQuestionByExams');
Route::get('professor/getQuestionDetails','api\QuestionController@getQuestionDetails');

Route::delete('professor/deleteQuestion','api\QuestionController@destroyQuestion');
/////////////////////:
/// Correction
Route::get('professor/getReponseEtudiantOfExam','api\ReponseEtudiantCorrectionController@getReponseEtudiantOfExam');
Route::post('professor/getCorrectionEtudiantOfExam','api\ReponseEtudiantCorrectionController@getCorrectionEtudiantOfExam');
Route::get('professor/getNotesEtudiantOfExam','api\ReponseEtudiantCorrectionController@getNotesEtudiantOfExam');



/////////////
//////////////////////////////////////////// Professeur

// Creation d'une route pour envoie de données
Route::post('/professeur/create','api\ProfesseurController@createProfesseur');

// Creation d'une route pour reception de données
Route::get('/professeur/liste','api\ProfesseurController@fetchProfesseurs');

// Creation d'une route pour reception de données avec condition d'identifiant
Route::get('/professeur','api\ProfesseurController@fetchProfesseurbyid');

// Creation d'une route pour la modification de données
Route::put('/professeur/update','api\ProfesseurController@updateProfesseur');

// Creation d'une route pour la supression d'un enregistrement
Route::delete('/professeur/delete/{id}','api\ProfesseurController@deleteProfesseur');

/////////////

//////////////////////////////////////////// Etudiant

// Creation d'une route pour envoie de données
Route::post('/etudiant/create','api\EtudiantController@createEtudiant');

// Creation d'une route pour reception de données
Route::get('/etudiant/liste','api\EtudiantController@fetchEtudiants');

// Creation d'une route pour reception de données avec condition d'identifiant
Route::get('/etudiant','api\EtudiantController@fetchEtudiantbyid');

// Creation d'une route pour la modification de données
Route::put('/etudiant/update','api\EtudiantController@updateEtudiant');

// Creation d'une route pour la supression d'un enregistrement
Route::delete('/etudiant/delete/{id}','api\EtudiantController@deleteEtudiant');

/////////////

//////////////////////////////////////////// Notification

// Creation d'une route pour creation d'une notification
Route::post('/notification/create','api\NotificationController@createNotification');

// Creation d'une route pour reception de la liste des notifs
Route::get('/notification/liste','api\NotificationController@fetchNotifications');

// Creation d'une route pour reception de données avec condition d'identifiant de la notif
Route::get('/notification/{id}','api\NotificationController@fetchNotificationbyid');

// Creation d'une route pour reception de données avec condition d'identifiant de lutilisateur
Route::get('/notification/listebyuserid/{user_id}','api\NotificationController@fetchNotificationbyUserid');

// Creation d'une route pour la modification de données d'une notification
Route::put('/notification/update','api\NotificationController@updateNotification');

// Creation d'une route pour la supression d'une notificattion
Route::delete('/notification/delete/{id}','api\NotificationController@deleteNotification');

/////////////


//////////////////////////////////////////// User

// Creation d'une route pour la mdification du mot de passe
Route::post('/user/create','api\UserController@createUser');

// Creation d'une route pour la mdification du mot de passe
Route::put('/user/update/password','api\UserController@changePassword');

/////////////


//////////////////////////////////////////// Reclamation

// Creation d'une route pour envoie de données
Route::post('/reclamation/create','api\ReclamationController@createReclamation');
// Creation d'une route pour reception des reclamations traité
Route::get('/reclamation/listetraite','api\ReclamationController@fetchReclamationsTraite');

// Creation d'une route pour reception des reclamations non traité
Route::get('/reclamation/listenontraite','api\ReclamationController@fetchReclamationsNonTraite');

// Creation d'une route pour reception de données
Route::get('/reclamation/liste','api\ReclamationController@fetchReclamations');

// Creation d'une route pour reception de données avec condition d'identifiant
Route::get('/reclamation/{id}','api\ReclamationController@fetchReclamationbyid');

// Creation d'une route pour la modification de données
Route::put('/reclamation/update','api\ReclamationController@updateReclamation');

// Creation d'une route pour la supression d'un enregistrement
Route::delete('/reclamation/delete/{id}','api\ReclamationController@deleteReclamation');

/////////////


//routes des fonctions d'examen pour etudiant
//route pour charger les examen futur d'un etudiant
Route::get('etudiant/examens','api\ExamenController@findFutureExamByEtudiant');

//route pour charger les question d'un examen
Route::get('etudiant/examen','api\ExamenController@loadExam');

//route pour envoyer les reponses et les traiter
Route::post('etudiant/reponses','api\ReponseEtudiantController@getAnswers');

//route pour charger les examen deja passé par l'etudiant
Route::get('etudiant/reponses/examens/','api\ReponseEtudiantController@getClassesOfPassedExams');

//route pour charger les reponses d'un etuduant a un examen
Route::get('etudiant/reponses/examens/examen','api\ReponseEtudiantController@getReponsesOfStudent');

// route pour charger les notes d'un etudiant dans les examen corrigés
Route::get('etudiant/notes','api\ExamenController@findExamsWithNote');


//////////////////////////////////////////////////////////////////////////:
///
Route::get('professor/charts/classes','api\ClasseController@findbyProfApiToken');

Route::get('professor/charts/meanmarkbyexam','api\ClasseController@getMeansMarksByExam');

Route::get('professor/charts/nb_exams_per_class','api\ExamenController@getNbExamsByClassOfProfessor');

Route::get('professor/charts/nb_std_per_class','api\ClasseController@getNbStudentsByClassOfProfessor');

Route::get('professor/charts/note_std_per_exam','api\ExamenController@getnoteByExamOfStudent');
