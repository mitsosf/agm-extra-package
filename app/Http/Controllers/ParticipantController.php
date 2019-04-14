<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Mail\PaymentConfirmation;
use App\Room;
use App\Roomsize;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Everypay\Everypay;
use Everypay\Payment;
use Everypay\Token;
use function GuzzleHttp\Promise\all;
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

    public function deposit()
    {

        $user = Auth::user();
        $error = null;

        /* Check if user has already paid the deposit
         * Paid = 1
         * Not paid = 0
         * Something weird = Whatever
        */

        $deposit_check = $user->transactions->where("type", "deposit")->count();

        return view('participants.deposit', compact('user', 'error', 'deposit_check'));
    }

    public function parseToken()
    {
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
                    $deposit_check = $user->transactions->where("type", "deposit")->count();
                    return view('participants.home', compact('error', 'user', 'deposit_check'));
                }
                //If all works
                Session::put('token', $token);
                return redirect(route('participant.deposit.charge'));
            } else {
                //If we don't receive the token_details
                $error = "An error has occurred, please try again (Error 100)";
                $deposit_check = $user->transactions->where("type", "deposit")->count();
                return view('participants.deposit', compact('user', 'error', 'deposit_check'));
            }
        }

        //If we don't receive a token
        $error = "An error has occurred, please try again (Error 101)";
        $deposit_check = $user->transactions->where("type", "deposit")->count();
        return view('participants.payment', compact('user', 'error', 'deposit_check'));
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
            $description = 'Deposit--' . $user->id . "." . $user->name . " " . $user->surname . "--" . $user->esn_country . "/" . $user->section;

            $payment = Payment::create(array(
                "amount" => 2000, //Amount in cents
                "currency" => "eur", //Currency
                "token" => $token,
                "description" => 'Extra: '.$description,
                "capture" => 0  //Authorize card only
            ));
            Session::forget('token');

            if (isset($payment->token)) {

                //If all goes well and user is charged
                //Save deposit to db
                $deposit = new Transaction();
                $deposit->type = 'deposit';
                $deposit->amount = $payment->amount / 100;
                $deposit->approved = 0;
                $deposit->comments = 'card';
                $deposit->proof = $payment->token;
                $deposit->user()->associate($user);
                $deposit->save();


                //Display success message on homepage
                Session::flash('paid_fee', 1);
                return redirect(route('participant.home'));
            } else {
                $error = "Your card issuer didn't approve the payment. If this problem persists, please try using a different card (Error 103)";
                $deposit_check = $user->transactions->where("type", "deposit")->count();
                return view('participants.deposit', compact('user', 'error', 'deposit_check'));
            }
        } else {
            //If validation succeeds but pre-charging fails
            $error = "An error has occurred, please try again (Error 102)";
            $deposit_check = $user->transactions->where("type", "deposit")->count();
            return view('participants.deposit', compact('user', 'error', 'deposit_check'));
        }
    }

    public function delegation()
    {
        $user = Auth::user();
        if (substr($user->comments, 0, 2) !== "NR") {
            return redirect(route('participant.home'));
        }

        $participants = User::where('esn_country', $user->esn_country)->whereIn('spot_status', ['paid', 'approved'])->get();

        return view('participants.delegation', compact('participants'));
    }

    public function generateProof()
    {
        return Auth::user()->generateProof();
    }


    public function rooming()
    {
        if (env('EVENT_ROOMING') == '0') {

        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }

        $roomIsFinal = false;
        $user = Auth::user();

        $room = 0;

        if (isset($user->room)) {
            $room = $user->room;
            if ($room->final) {
                $roomIsFinal = true;
            }

            $roommates = User::where('room_id', $room->id)->get();
        }


        return view('participants.rooming', compact('room', 'roomIsFinal', 'roommates'));
    }

    public function createRoomShow()
    {

        if (env('EVENT_ROOMING') == '0') {
            return redirect(route('participant.home'));
        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }
        $user = Auth::user();

        //Compute available bed sizes
        $beds = array();
        $roomSizes = Roomsize::where('hotel_id', 1)->get();

        foreach ($roomSizes as $roomSize) {
            $occupied_rooms_count = Room::where('hotel_id', 1)->where('beds', $roomSize->size)->where('final', 1)->get()->count();
            if ($occupied_rooms_count < $roomSize->quantity) {
                array_push($beds, $roomSize->size);
            }
        }

        if (is_null($user->room)) {
            return view('participants.createRoom', compact('user', 'beds'));
        }

        return redirect(route('participant.rooming'));

    }

    public function createRoom(Request $request)
    {

        if (env('EVENT_ROOMING') == '0') {
            return redirect(route('participant.home'));
        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }
        $size = $request['size'];
        $comments = $request['comments'];

        //Check if user has room
        $user = Auth::user();
        if (is_null($user->room_id)) {
            //Check to see if roomsize is still available
            $total_rooms = Roomsize::where('hotel_id', 1)->where('size', $size)->first()->quantity;
            $occupied_rooms = Room::where('hotel_id', 1)->where('final', 1)->where('beds', $size)->get()->count();

            if ($occupied_rooms >= $total_rooms) {

                Session::flash('error', 'Roomsize not available');
                return redirect(route('participant.rooming'));
            }

            //Create room
            $room = new Room();
            $room->hotel_id = 1;
            $room->beds = $size;
            $room->code = rand(111111, 999999);
            $room->save();

            $user->room_id = $room->id;
            $user->rooming = 1;
            $user->rooming_comments = 'ROOMING-' . $comments . '-' . $user->rooming_comments;
            $user->update();

            return redirect(route('participant.rooming'));

        } else {
            Session::flash('error', 'User already has room');
            return redirect(route('participant.rooming'));
        }
    }

    public function joinRoomShow()
    {

        if (env('EVENT_ROOMING') == '0') {
            return redirect(route('participant.home'));
        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }
        $user = Auth::user();

        if (is_null($user->room)) {
            return view('participants.joinRoom', compact('user'));
        }

        return redirect(route('participant.rooming'));
    }

    public function joinRoom(Request $request)
    {

        if (env('EVENT_ROOMING') == '0') {
            return redirect(route('participant.home'));
        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }
        $id = $request['id'];
        $code = $request['code'];

        //Check if participant is already in a room
        $user = Auth::user();
        if (!is_null($user->room_id)) {
            Session::flash('error', 'You already have a room');
            return redirect(route('participant.rooming'));
        }

        //Check if room exists
        $room = Room::find($id);
        if (isset($room)) {
            //Check if room is full
            if (!$room->final) {
                //Check if code is correct
                if ($room->code === $code) {
                    //Register participant in the room
                    $user->room_id = $id;
                    $user->rooming = 1;
                    $user->update();

                    //Check if the room is now full
                    $count = User::where('room_id', $id)->get()->count();
                    if ($count == $room->beds) {
                        $room->final = true;
                        $room->update();

                        //Check if final room of this size
                        $beds = $room->beds;
                        $fullRoomCount = Room::where('hotel_id', 1)->where('beds', $beds)->where('final', '1')->get()->count();
                        $availableRoomCount = Roomsize::where('hotel_id', 1)->where('size', $beds)->first()->quantity;

                        //If the room in question is actually the last of its kind in this hotel, delete all similar rooms and disassociate users
                        if ($fullRoomCount >= $availableRoomCount) {
                            $pendingRooms = Room::where('hotel_id', 1)->where('beds', $beds)->where('final', 0)->get();
                            foreach ($pendingRooms as $room) {
                                //Nullify all participants' rooming and then delete the room
                                $occupants = User::where('room_id', $room->id)->get();

                                //Reset participants
                                foreach ($occupants as $occupant) {
                                    $occupant->room_id = null;
                                    $occupant->rooming = 0;
                                    $occupant->update();
                                    //TODO maybe notify users via email in the future
                                    Mail::raw('The room you created has been unfortunately been deleted. This is because as all the rooms of that size that were initially available have been filled by other participants before you managed to fill yours. We would like to ask you to please create another room of a different size or join a vacant room. See you soon in Thessaloniki!', function ($message) use ($occupant) {
                                        $message->subject('Thessaloniki Castaways Rooming platform')->to($occupant->email);
                                    });
                                }

                                //Delete room
                                $room->delete();
                            }
                        }
                    }

                    return redirect(route('participant.rooming'));
                } else {
                    Session::flash('error', 'Incorrect room code');
                    return redirect(route('participant.rooming'));
                }
            } else {
                Session::flash('error', 'Room is full');
                return redirect(route('participant.rooming'));
            }
        } else {
            Session::flash('error', 'Room doesn\'t exist');
            return redirect(route('participant.rooming'));
        }
    }

    public
    function randomRoomShow()
    {

        if (env('EVENT_ROOMING') == '0') {
            return redirect(route('participant.home'));
        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }
        $user = Auth::user();

        if (is_null($user->room)) {
            return view('participants.randomRoom', compact('user'));
        }

        return redirect(route('participant.rooming'));
    }

    public
    function randomRoom(Request $request)
    {

        if (env('EVENT_ROOMING') == '0') {
            return redirect(route('participant.home'));
        }

        if (Auth::user()->spot_status !== 'paid') {
            return redirect(route('home'));
        }
        $comments = $request['comments'];

        $user = Auth::user();
        $user->rooming_comments = 'RANDOM-' . $comments . '-' . $user->rooming_comments;
        $user->rooming = 1;
        $user->update();
        return redirect(route('participant.rooming'));
    }

    public function leaveRoom(){

        $user = Auth::user();
        $user->rooming = 0;
        if (substr($user->rooming_comments,0,6) == "RANDOM"){
            //Random room
            $user->rooming_comments = $str = substr($user->rooming_comments, 8);
        }else{
            $user->room_id = null;
        }

        $user->update();
        Session::flash('error','You have left the room');

        return redirect(route('participant.rooming'));
    }

    public
    function logout()
    {
        Auth::logout();
        return redirect(route('home'));
    }
}
