<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function registrationShow()
    {
        return view('participants.registrationForm');
    }

    public function registration(Request $request)
    {
        //Validate request
        $this->validate($request, [
            'document' => 'required|max:255',
            'phone' => 'required|max:255',
            'facebook' => 'required||max:255',
            'meal' => 'max:255',
            'esncard' => 'max:255',
            'tshirt' => 'required',
            'allergies' => 'max:255',
            'comments' => 'max:255',
            'consent' => 'required',
            'gdpr' => 'required'
        ]);

        $user = Auth::user();
        $user->document = $request['document'];
        $user->phone = $request['phone'];
        $user->esncard = $request['esncard'];
        $user->tshirt = $request['tshirt'];
        $user->facebook = $request['facebook'];
        $user->rooming_comments = $request['comments'];
        $user->allergies = $request['allergies'];
        $user->meal = $request['meal'];
        $user->registration = true;
        $user->update();

        return redirect(route('participant.payment'));

    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('home'));
    }
}
