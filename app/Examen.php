<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    //
    public function questions(){
        return $this->hasMany(Question::class );
    }

    public function classe(){
        return $this->belongsTo(Classe::class );
    }

    public function etudiants(){
        return $this->belongsToMany(Etudiant::class,'etudiant_examens');
    }
}
