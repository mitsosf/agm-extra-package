@extends('layouts.participant.master')

@section('content')
     <div style="text-align: center;margin-top: 10%">
          <h3>Join existing room</h3>
          <p> Here you can join a room that has already been created by another participant. <h4>Please ask them to log in
               to their account and give you the <b><u>Room ID</u></b> AND 6-digit <b><u>Room code</u></b>.</h4></p>
          <p>If you have the Room ID and 6-digit Room Code, please enter them in the fields below.</p>


          <div class="container">
               <form method="POST" action="{{ route('participant.rooming.join') }}">
                    <div class="row" style="margin-top: 2%">
                         <div class="col-md-3"></div>
                         <div class="col-md-3">
                              <div class="form-group{{ $errors->has('id') ? ' has-error' : '' }}"
                                   style="text-align: left">
                                   <label for="id" class="control-label">Room ID:*</label>
                                   <input value="{{ old('id') }}" style="width: 40%; text-align: left" class="form-control"
                                          placeholder="eg. 34"
                                          id="id"
                                          name="id">
                              </div>
                         </div>
                         <div class="col-md-3">
                              <div class="form-group{{ $errors->has('code') ? ' has-error' : '' }}"
                                   style="text-align: left">
                                   <label for="code" class="control-label">Code:*</label>
                                   <input value="{{ old('code') }}" style="width: 70%; text-align: left"
                                          class="form-control"
                                          placeholder="eg. 123456" id="code" name="code">
                              </div>
                         </div>
                         <div class="col-md-3"></div>
                    </div>
                    @csrf
                    <div id="submitOrBack" style="margin-top: 2%">
                         <input class="btn btn-primary" type="submit" value="Join Room">
                         <a class="btn btn-danger" href="{{route('participant.rooming')}}">Back</a>
                    </div>
               </form>
          </div>
     </div>
@endsection