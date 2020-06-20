<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EtudiantExamen extends Model
{

    public function examen(){
        return $this->belongsTo(Examen::class );
    }
}
