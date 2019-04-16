<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\App;
use Ixudra\Curl\Facades\Curl;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;

/**
 * @property mixed id
 * @property string username
 * @property mixed email
 * @property mixed photo
 * @property mixed name
 * @property mixed surname
 * @property mixed esn_country
 * @property mixed birthday
 * @property mixed section
 * @property mixed gender
 * @property mixed facebook
 * @property string spot_status
 * @property int role_id
 * @property mixed fee
 * @property mixed transactions
 * @property mixed rooming_comments
 * @property string document
 * @property string phone
 * @property mixed allergies
 * @property  string esncard
 * @property  string tshirt
 * @property  string meal
 * @property mixed comments
 * @property int checkin
 */

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'surname', 'email', 'role_id', 'section', 'esncard', 'document', 'birthday', 'gender', 'phone', 'esn_country', 'photo', 'tshirt', 'facebook', 'allergies', 'comments', 'workshops', 'fee', 'meal', 'rooming', 'spot_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function room()
    {
        return $this->belongsTo('App\Room');
    }

    public function esnCardStatus($card)
    {

        $response = Curl::to('https://esncard.org/services/1.0/card.json')
            ->withData(array('code' => $card))
            ->get();


        if (strpos($response, 'active')) {
            return 'active';
        } elseif (strpos($response, 'expired')) {
            return 'expired';
        } elseif (strpos($response, 'available')) {
            return 'available';
        } else {
            return 'invalid';
        }
    }

    public function refreshErsStatus()
    {

        if ($this->spot_status === 'paid') {
            $status = 'paid';
        } else {
            $status = 'approved'; //Default status
            $this->spot_status = $status;
            $this->update();
        }
        return $status;

    }

    public function generateProof(){

        $user = $this;

        $transactions = $user->transactions()->where('type', 'fee')->with('invoice')->get();

        $invoice = null;
        if ($transactions->count() > 0) {
            $invoice = $transactions->first()->invoice;
        }else{
            return "Invoice is being processed, please check again later";
        }

        $invID = $invoice->id;

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('mails.paymentConfirmation',compact('user', 'invID')));

        return $pdf->stream();
    }

    public function isAlumni(){
        if ($this->comments === "alumni"){
            return true;
        }

        return false;
    }

    /**
     * @return array with debt transaction and amount if there is a debt transaction or just the amount
     */
    public function calculateDebt(){
        $amount = 0;

        if (is_null(Transaction::where('user_id',$this->id)->where('type', 'deposit')->where('approved','1')->first())){
            //If user has not deposited
            $amount+=20;
        }

        $debt = Transaction::where('user_id',$this->id)->where('type','debt')->where('approved','0')->first();
        if (!is_null($debt)){
            //If user owes us money
            $amount+=$debt->amount;

            return array(
                "transaction" => $debt,
                "amount" => $amount
            );
        }

        return array(
            "transaction" => null,
            "amount" => $amount
        );
    }

}
