<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReponseEtudiant extends Model
{
    //
    public function etudiant(){
        return $this->belongsTo(Etudiant::class );
    }

    public function question(){
        return $this->belongsTo(Question::class );
    }
}
