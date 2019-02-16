@extends('layouts.app')

@section('background')style='background: #F8F8F8'@endsection

@section('content')
    <div style="text-align: center;margin-top: 2%">
        <h2 style="color: #222059">Ready to</h2>
        <h2 style="color: #222059">#SailWithUs?</h2>
        <img src="{{asset('images/castaways_logo.png')}}" style="width: 40%;height: 40%;">
        <h3 style="margin-top: 3%;margin-bottom: 2%"><a href="{{route('cas.login')}}" class="btn btn-success">Login using ESN Accounts</a></h3>
        <div style="text-align: center;color: #222059"><h6>By logging in, I accept the <a href="{{route('terms')}}" target="_blank"><b>terms of use</b></a><br> and the privacy policies <a target="_blank" href="https://esngreece.gr/privacy-policy">[1]</a><a
                        href="https://accounts.esn.org/privacy-policy" target="_blank">[2]</a>.</h6></div>
    </div>


@endsection


