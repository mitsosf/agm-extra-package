@extends('layouts.oc.master')

@section('content')
    <h4>User info:</h4>
    <div style="text-align: center">
        <a href="{{$user->photo}}" target="_blank"><img src="{{$user->photo}}" alt="User photo" width="10%"></a>
        <h4>Name: <a href="{{route('oc.user.show',$user)}}">{{$user->name. " ". $user->surname}}</a></h4>
        <h4>Country: {{$user->esn_country}}</h4>
        <h4>Section: {{$user->section}}</h4>
        <h4>Price: {{$transaction->amount}}</h4>
        <a class="btn btn-info" style="margin-bottom: 2%" href="{{$transaction->proof}}" target="_blank" >Show proof</a>
        <form action="{{route('oc.transaction.approve')}}" method="POST">
            @method('PUT')
            <label for="debt">Debt:</label>
        @if ($errors->has('debt'))
                <span class="help-block"><strong style="color: red;">{{ $errors->first('debt') }}</strong></span>
            @endif
            <input id="debt" name="debt" type="text" placeholder="eg. 4"><br>
            <input id="transaction" name="transaction" type="hidden" value="{{$transaction->id}}">
            @csrf <br>
            <input class="btn btn-success" type="submit" value="Approve">
            <a class="btn btn-danger" href="{{route('oc.cashflow.bank')}}">Cancel</a>
        </form>
    </div>
@endsection