<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    public function hotel()
    {
        return $this->belongsTo('App\Hotel');
    }

    public function users()
    {
        return $this->hasMany('App\User');
    }

    protected $dates = ['deleted_at'];

}
