<?php

namespace App\Http\Controllers;

use App\Hotel;
use App\Room;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\VarDumper\Caster\TraceStub;

class CheckinController extends Controller
{
    public function __construct()
    {
        $this->middleware('checkin');
    }

    public function index()
    {
        return view('checkin.home');
    }

    public function hotel(Hotel $hotel)
    {
        $residents = User::whereHas('room', function ($query) use ($hotel) {
            $query->where('hotel_id', $hotel->id)->where('final',1);
        })->get();

        $checkedIn = $residents->where('checkin', '!=', 0);

        return view('checkin.hotel', compact('hotel', 'residents', 'checkedIn'));
    }

    public function validation(Hotel $hotel, User $user)
    {
        $debt = $user->calculateDebt();
        return view('checkin.validation', compact('user', 'debt', 'hotel'));
    }

    public function checkin(Hotel $hotel, User $user)
    {
        if ($user->checkin == 0) {
            //Charge balance to checkiner
            $debt = $user->calculateDebt();

            //Save checkin as transaction
            $checkin = new Transaction();
            $checkin->user_id = $user->id;
            $checkin->type = "checkin";
            $checkin->comments = Auth::user()->id; //ID of checkiner
            $checkin->amount = $debt['amount'];
            $checkin->approved = $debt['amount'] == '0' ? 1 : 0; //Automatically approve checkin transaction if no debt
            $checkin->save();

            $deposit = Transaction::where('user_id', $user->id)->where('type', 'deposit')->first();
            if (is_null($deposit)) {
                $deposit = new Transaction();
                $deposit->user_id = $user->id;
                $deposit->type = "deposit";
                $deposit->proof = Auth::user()->id; //ID of checkiner
                $deposit->amount = 50;
                $deposit->approved = 1;
                $deposit->save();
            }

            //Nullify debt
            if (!is_null($debt['transaction'])) {
                $debt = $debt['transaction'];
                $debt->approved = 1;
                $debt->update();
            }

            //Check user in
            $user->checkin = 1;
            $user->update();
            return redirect(route('checkin.hotel', $hotel));

        } elseif ($user->checkin == 1) {

            if (Auth::user()->role_id!=2){
                return redirect(route('checkin.hotel', $hotel));
            }
            //Check user in
            $user->checkin = 0;
            $user->update();
            return redirect(route('checkin.hotel', $hotel));

        } else {
            return redirect(route('checkin.hotel', $hotel));
        }
    }

    public function funds()
    {
        $funds = [
            'cash' => 0,
            'deposited' => 0,
            'all' => 0,
        ];

        //Calculate all funds received by checkiner
        $deposits = Transaction::where('type', 'deposit')->where('proof', Auth::user()->id)->get();
        foreach ($deposits as $deposit) {
            $funds['all'] += $deposit->amount;
        }

        $transactions = Transaction::where('user_id', Auth::user()->id)->where('type', 'oc')->where('approved', 1)->get();
        foreach ($transactions as $transaction) {
            $funds['deposited'] += $transaction->amount;
        }

        $funds['cash']= $funds['all'] - $funds['deposited'];

        $oc_transactions = Transaction::where('user_id', Auth::user()->id)->where('type', 'oc')->get();

        return view('checkin.funds', compact('funds', 'oc_transactions'));
    }

    public function createDepositPickupRequestShow()
    {
        $funds = [
            'cash' => 0,
            'deposited' => 0,
            'all' => 0,
        ];

        //Calculate all funds received by checkiner
        $deposits = Transaction::where('type', 'deposit')->where('proof', Auth::user()->id)->get();
        foreach ($deposits as $deposit) {
            $funds['all'] += $deposit->amount;
        }

        $transactions = Transaction::where('user_id', Auth::user()->id)->where('type', 'oc')->where('approved', 1)->get();
        foreach ($transactions as $transaction) {
            $funds['deposited'] += $transaction->amount;
        }

        $funds['cash']= $funds['all'] - $funds['deposited'];
        $cash = $funds['cash'];


        return view('checkin.depositPickupRequest', compact('cash'));

    }

    public function createDepositPickupRequest(Request $request)
    {
        //Check if amount is valid
        $amount = $request['amount'];
        $cash = $request['cash'];

        //If they hold enough cash
        if ($amount <= $cash) {
            $deposit = new Transaction();
            $deposit->user_id = Auth::user()->id;
            $deposit->type = 'oc';
            $deposit->amount = $amount;
            $deposit->approved = 0;
            $deposit->save();
        } else {
            return redirect(route('checkin.funds.createRequest.show'));
        }


        return redirect(route('checkin.funds'));
    }


    public function logout()
    {
        Auth::logout();
        return redirect(route('home'));
    }
}
