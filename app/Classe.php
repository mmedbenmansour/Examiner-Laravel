<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    public function professeur(){
        return $this->belongsTo(Professeur::class);
    }

    public function etudiants(){
        return $this->belongsToMany(Etudiant::class,'classe_etudiant');
    }
    public function examens(){
        return $this->hasMany(Examen::class );
    }
}
