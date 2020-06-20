<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    protected $table = 'reclamations';
    protected $fillable = ['id', 'sujet','contenu','reponse', 'user_id', 'admin_id'];

    public function user(){
        return $this->belongsTo(User::class );
    }

    public function admin(){
        return $this->belongsTo(Admin::class );
    }

}
