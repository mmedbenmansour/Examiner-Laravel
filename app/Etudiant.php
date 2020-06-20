<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $fillable = [
        'id','nom','prenom', 'user_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function classes(){
        return $this->belongsToMany(Classe::class,'classe_etudiant');
    }
    public function reponseEtudiants(){
        return $this->hasMany(ReponseEtudiant::class );
    }

    public function examens(){
        return $this->belongsToMany(Examen::class,'etudiant_examens');
    }
}
