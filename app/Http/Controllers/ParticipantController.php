<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Mail\PaymentConfirmation;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Everypay\Everypay;
use Everypay\Payment;
use Everypay\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ParticipantController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('participant');
    }

    public function index()
    {
        $user = Auth::user();
        $error = null;

        $debt = $user->transactions->where('type', 'debt')->where('approved', 0)->first();

        $payments = 0;
        $approved_transactions_count = Transaction::where('type', 'fee')->where('approved', '1')->get()->count();
        if (env('EVENT_LIMIT') - $approved_transactions_count > 0) {
            $payments = 1;
        }


        return view('participants.home', compact('user', 'error', 'debt', 'payments'));
    }

    public function payment()
    {

        if (env('EVENT_PAYMENTS') == '0') {
            return redirect(route('participant.home'));
        }

        //If Alumni
        if (Auth::user()->isAlumni()) {

            $user = Auth::user();
            $error = null;

            $transactions = $user->transactions->where('type', 'fee');

            $invoice = null;
            if ($transactions->count() > 0) {
                $invoice = $transactions->first()->invoice;
            }
        } else {
            //If not Alumni
            $approved_transactions_count = Transaction::where('type', 'fee')->where('approved', '1')->get()->count();

            if (env('EVENT_LIMIT') - $approved_transactions_count <= 0) {
                return redirect(route('home'));
            }

            $user = Auth::user();
            $error = null;

            $transactions = $user->transactions->where('type', 'fee');

            $invoice = null;
            if ($transactions->count() > 0) {
                $invoice = $transactions->first()->invoice;
            }
        }
        return view('participants.payment', compact('user', 'error', 'invoice'));
    }

    //TODO log ALL errors
    //TODO reassure the plembs that they have not been charged, if an error occurs
    public function validateCard()
    {
        if (env('EVENT_PAYMENTS') == '0') {
            return redirect(route('participant.home'));
        }

        $approved_transactions_count = Transaction::where('type', 'fee')->where('approved', '1')->get()->count();
        if (!Auth::user()->isAlumni()) {
            if (env('EVENT_LIMIT') - $approved_transactions_count <= 0) {
                return redirect(route('home'));
            }
        }

        //Set up the private key
        Everypay::setApiKey(env('EVERYPAY_SECRET_KEY'));

        //Get token from submission
        $token = $_POST['everypayToken'];
        $user = Auth::user();
        if (isset($token)) {
            //Check if card is not Visa, MasterCard or Maestro
            $token_details = Token::retrieve($token);
            if (isset($token_details->card)) {
                $type = $token_details->card->type;
                if ($type !== 'Visa' && $type !== 'MasterCard' && $type !== 'Maestro') { //Only accept Visa, MasterCard & Maestro
                    $error = 'Your card issuer is unsupported, please use either a Visa, MasterCard or Maestro';
                    return view('participants.payment', compact('error', 'user'));
                }
                Session::put('token', $token);
                //If all goes according to plan
                return redirect(route('participant.charge'));
            } else {
                //If we don't receive the token_details
                $error = "An error has occurred, please try again (Error 100)";
                return view('participants.payment', compact('error', 'user'));
            }
        }
        //If we don't receive a token
        $error = "An error has occurred, please try again (Error 101)";
        return view('participants.payment', compact('error', 'user'));
    }


    public function charge()
    {
        if (env('EVENT_PAYMENTS') == '0') {
            return redirect(route('participant.home'));
        }

        $approved_transactions_count = Transaction::where('type', 'fee')->where('approved', '1')->get()->count();
        if (!Auth::user()->isAlumni()) {
            if (env('EVENT_LIMIT') - $approved_transactions_count <= 0) {
                return redirect(route('home'));
            }
        }
        //Set up the private key
        Everypay::setApiKey(env('EVERYPAY_SECRET_KEY'));
        $user = Auth::user();
        $error = '';

        //Charge card
        $token = Session::get('token');
        if (isset($token)) {

            //Format desc
            $description = 'Extra: ' . $user->id . "." . $user->name . " " . $user->surname . "--" . $user->esn_country . "/" . $user->section;

            $payment = Payment::create(array(
                "amount" => env('EVENT_FEE', '16000'), //Amount in cents
                "currency" => "eur", //Currency
                "token" => $token,
                "description" => $description
            ));

            Session::forget('token');

            if (isset($payment->token)) {
                //TODO Check if transaction is correct

                //Update user info
                $user->fee = $payment->amount / 100;
                $user->fee_date = Carbon::now();
                $user->spot_status = 'paid';
                $user->update();


                //Generate PDF invoice, send it to the user and update DB

                //Generate PDF
                $pdf = App::make('dompdf.wrapper');
                $invID = Invoice::all()->count() + 1;
                $pdf->loadHTML(view('mails.paymentConfirmation', compact('user', 'invID')));
                //Save invoice locally
                $path = 'invoices/' . $invID . $user->name . $user->surname . $user->esn_country . 'Fee.pdf';

                //Save the whole transaction to the database
                //Create transaction
                $transaction = new Transaction();
                $transaction->user()->associate($user);
                $transaction->amount = $user->fee;
                $transaction->comments = null;
                $transaction->approved = true;
                $transaction->proof = $payment->token;
                $transaction->save();

                //Create invoice and attach to transaction
                $invoice = new Invoice();
                $invoice->path = $path;
                $invoice->esn_country = $user->esn_country;
                $invoice->section = $user->section;
                $invoice->transaction()->associate($transaction);
                $invoice->save();

                $pdf->save(env('APPLICATION_DEPLOYMENT_PATH_PUBLIC') . $path);
                //Send invoice to participant
                Mail::to($user->email)->send(new PaymentConfirmation($user, env('APPLICATION_DEPLOYMENT_PATH_PUBLIC') . $path));

                //If all goes well and user is charged
                Session::flash('paid_fee', 1);
                return redirect(route('participant.home'));
            } else {
                $error = "An error has occurred, please try again (Error 103)";
                return view('participants.payment', compact('user', 'error'));
            }
        } else {
            //If validation succeeds but charging fails
            $error = "An error has occurred, please try again (Error 102)";
            return view('participants.payment', compact('user', 'error'));
        }
    }

    //TODO test deposits by card

    public function deposit()
    {
        $user = Auth::user();
        $error = null;

        /* Check if user has already paid the deposit
         * Paid = 1
         * Not paid = 0
         * Something weird = Whatever
        */
        //DO NOT DISTURB, the beast will eat you alive
        $deposit_check = $user->withCount(
            ['transactions' => function ($query) {
                $query->where('type', 'deposit');
            }])->get()[0]->transactions_count;

        return view('participants.deposit', compact('user', 'error', 'deposit_check'));
    }

    public function parseToken()
    {
        //Set up the private key
        Everypay::setApiKey(env('EVERYPAY_SECRET_KEY'));

        //Get token from submission
        $token = $_POST['everypayToken'];

        //Check if card is not Visa, MasterCard or Maestro
        $token_details = Token::retrieve($token);
        $type = $token_details->card->type;
        if ($type !== 'Visa' && $type !== 'MasterCard' && $type !== 'Maestro') { //Only accept Visa, MasterCard & Maestro
            $error = 'Your card issuer is unsupported, please use either a Visa, MasterCard or Maestro';
            $user = Auth::user();
            return view('participants.home', compact('error', 'user'));
        }
        Session::put('token', $token);
        return redirect(route('participant.deposit.charge'));
    }

    public function chargeDeposit()
    {
        //Set up the private key
        Everypay::setApiKey(env('EVERYPAY_SECRET_KEY'));
        $user = Auth::user();
        $error = '';

        //Pre-charge card
        $token = Session::get('token');
        if (isset($token)) {

            //Format desc
            $description = 'Extra: Deposit--' . $user->id . "." . $user->name . " " . $user->surname . "--" . $user->esn_country . "/" . $user->section;

            $payment = Payment::create(array(
                "amount" => 5000, //Amount in cents
                "currency" => "eur", //Currency
                "token" => $token,
                "description" => $description,
                "capture" => 0  //Authorize card only
            ));
            Session::forget('token');

            if (isset($payment->token)) {
                //TODO Check if transaction is correct

                //Send mail to the user

                //If all goes well and user is charged
                //Save deposit to db
                $deposit = new Transaction();
                $deposit->type = 'deposit';
                $deposit->amount = $payment->amount / 100;
                $deposit->approved = 0;
                $deposit->proof = $payment->token;
                $deposit->user()->associate($user);
                $deposit->save();

                //TODO SERIALISE
                //event(new UserPaidDeposit($user));

                //Display success message on homepage
                Session::flash('paid_deposit', 1);
                return redirect(route('participant.home'));
            } else {
                $error = "An error has occurred, please try again (Error 103)";
                return view('participants.deposit', compact('user', 'error'));
            }
        } else {
            //If validation succeeds but pre-charging fails
            $error = "An error has occurred, please try again (Error 102)";
            return view('participants.deposit', compact('user', 'error'));
        }
    }

    public function delegation(){
        $user = Auth::user();
        if (substr($user->comments,0,2) !== "NR"){
            return redirect(route('participant.home'));
        }

        $participants = User::where('esn_country',$user->esn_country)->whereIn('spot_status',['paid','approved'])->get();

        return view('participants.delegation', compact('participants'));
    }

    public function generateProof()
    {
        return Auth::user()->generateProof();
    }


    public function test()
    {

    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('home'));
    }
}
