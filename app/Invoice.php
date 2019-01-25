<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string path
 * @property  integer user_id
 * @property  string section
 * @property  string esn_country
 * @property mixed transaction
 */

class Invoice extends Model
{
    protected $fillable = [
        'path', 'section', 'esn_country'
    ];

    public function transaction(){
        return $this->belongsTo('App\Transaction');
    }
}
