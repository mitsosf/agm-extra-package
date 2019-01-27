<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function terms()
    {
        return view('misc.terms');
    }

    public function eventterms()
    {
        return view('misc.eventTerms');
    }
}
