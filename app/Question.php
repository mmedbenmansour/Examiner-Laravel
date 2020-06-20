<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    //
    public function choices(){
        return $this->hasMany(Choice::class );
    }

    public function solution(){
        return $this->belongsTo(Solution::class );
    }

    public function reponseEtudiants(){
        return $this->hasMany(ReponseEtudiant::class );
    }

    public function examen(){
        return $this->belongsTo(Examen::class );
    }
}
