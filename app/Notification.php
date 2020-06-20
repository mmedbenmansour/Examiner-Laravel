<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /// Hajar Moutik
    protected $table = 'notifications';
    protected $fillable = ['id', 'texte', 'type_notification', 'vu_a', 'createur', 'user_id'];

    public function user(){
        return $this->belongsTo(User::class );
    }

}
