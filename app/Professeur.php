<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{  protected $fillable = [
    'id',  'nom','prenom', 'user_id'
];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function classes(){
        return $this->hasMany(Classe::class);
    }


}
