<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    public function rooms(){
        return $this->hasMany('App\Room');
    }

    public function roomsizes()
    {
        return $this->hasMany('App\Roomsize');
    }
}
