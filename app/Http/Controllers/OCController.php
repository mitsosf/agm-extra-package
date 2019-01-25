<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Everypay\Everypay;
use Everypay\Payment;

class OCController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('oc');
    }

    public function index()
    {

        //User stats
        $totalUsers = User::all()->count(); //All that have ever logged in
        $approvedUsers = User::where('spot_status', 'approved')->count();

        //Funds stats
        $paidUsers = User::where('fee', '!=', '0')->get();
        $funds = 0;
        foreach ($paidUsers as $user) {
            $funds += $user->fee;
        }

        $paidUsersCount = $paidUsers->count();

        //Rooming stats
        //TODO CHANGE TO ROOMS
        $roomedUsers = User::where('rooming', '!=', 'No')->count();

        //Check-in stats
        $checkedInUsers = User::where('checkin', '!=', '0')->count();


        return view('oc.home', compact('totalUsers', 'approvedUsers', 'roomedUsers', 'funds', 'paidUsersCount', 'checkedInUsers'));
    }

    public function approved()
    {
        $users = User::where('spot_status', 'approved')->orWhere('spot_status', 'paid')->get();

        return view('oc.approved', compact('users'));
    }

    public function cashflow()
    {
        $transactions = Transaction::where('type', 'fee')->whereNull('comments')->get();

        $card_count = $transactions->count();

        $card_income = $transactions->sum('amount');

        return view('oc.cashflowCard', compact('transactions', 'card_income', 'card_count'));
    }

    public function cashflowDeposits()
    {
        $deposits = Transaction::where('type', 'deposit')->where('comments', 'card')->get();

        $deposit_amount = $deposits->sum('amount');
        $deposit_count = $deposits->count();


        $card_deposits = Transaction::where('type', 'deposit')->where('comments', 'card')->get();
        return view('oc.cashflowDeposits', compact('deposits', 'deposit_amount', 'deposit_count', 'card_deposits'));
    }

    public function acquireDeposit(Transaction $transaction)
    {

        //If transaction isn't a deposit
        if ($transaction->type !== 'deposit') {
            return redirect(route('oc.cashflow.deposits'));
        }

        Everypay::setApiKey(env('EVERYPAY_SECRET_KEY'));

        $payment = Payment::capture($transaction->proof);

        if (isset($payment->token)) { //If payment is successful

            $transaction->approved = 1;
            $transaction->comments = 'Acquired by' . Auth::user()->surname;
            $transaction->update();
            $transaction->delete();

            return redirect(route('oc.cashflow.deposits'));
        }
        return dd($payment);
    }

    public function refundDeposit(Transaction $transaction)
    {

        //If transaction isn't a deposit
        if ($transaction->type !== 'deposit') {
            return redirect(route('oc.cashflow.deposits'));
        }

        Everypay::setApiKey(env('EVERYPAY_SECRET_KEY'));

        $payment = Payment::refund($transaction->proof);

        if (isset($payment->token)) { //If payment is successful

            $transaction->delete();

            return redirect(route('oc.cashflow.deposits'));
        }

        return dd($payment);
    }

    public function transaction(Transaction $transaction)
    {
        return view('oc.transaction', compact('transaction'));
    }

    public function user(User $user)
    {
        return view('oc.user', compact('user'));
    }

    public function editUserComments(Request $request)
    {
        $user = User::find($request['user']);
        $user->comments = $request['comments'];
        $user->update();

        return redirect(route('oc.user.show', $user));
    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('home'));
    }
}
