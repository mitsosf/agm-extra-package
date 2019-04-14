<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roomsize extends Model
{
    protected $fillable = [
        'hotel_id', 'name', 'size'
    ];

    public function hotel(){
        return $this->belongsTo('App\Hotel');
    }
}
