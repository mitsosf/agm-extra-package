@extends('layouts.participant.master')

@section('content')
    @if(Auth::user()->rooming == 0)
        <div style="text-align: center;margin-bottom: auto">
            <h3>Rooming Platform</h3>


            <p>Here you can create a room or join one.</p>
            <p style="margin: auto">Please keep in mind that <b><u>we can not guarantee that the room size you choose
                        will be available or that you will be in the room with the people you choose, but
                        we will do our best to honor your wishes.</u></b></p>
            <p>The rooming platform will be open until <b style="color: red">Saturday 13/04/2019@23:59 (EEST)</b></p>
            <a class="btn btn-success" href="{{route('participant.rooming.create.show')}}">Create Room</a>
            <a class="btn btn-primary" href="{{route('participant.rooming.join.show')}}">Join Room</a>
            <a class="btn btn-warning" href="{{route('participant.rooming.random.show')}}">Random Room</a>
            @if(Session::has('error'))
                <h3 style="color: red">{{Session::get('error')}}</h3>
            @endif
            <div class="hidden-lg hidden-md hidden-print" style="width: 100%;margin: 3% auto 0;">
                <img id="example" src="#" width="100%" height="auto" alt="">
            </div>
            <div class="hidden-xs hidden-sm" style="width: 60%;margin: 3% auto 0;">
                <img id="example" src="#" width="100%" height="auto" alt="">
            </div>
        </div>
    @elseif(substr(Auth::user()->rooming_comments,0,6) == "RANDOM")
        <div style="text-align: center;margin-bottom: auto">
            <h3>Rooming Platform</h3>
            <p>You have selected a random room!</p>
            <p>You successfully registered for a random room. You will be informed once we'll assign you your
                roommates.</p>

            <p><a class="btn btn-warning" href="{{route('participant.rooming.leave')}}">Leave room</a></p>

        </div>
    @elseif($roomIsFinal)
        <div style="text-align: center;margin-bottom: auto">
            <h3>Rooming Platform</h3>
            <p>Your room preference is registered</p>
            The current occupants of this room are:
            <div class="container">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="hidden-xs">#</th>
                        <th>Name</th>
                        <th class="hidden-xs">ESN Country</th>
                        <th class="hidden-xs">ESN Section</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $index = 1?>
                    @foreach($roommates as $roommate)
                        <tr>
                            <td class="hidden-xs">{{$index}}</td>
                            <td><a href="#"> {{$roommate->name.' '.$roommate->surname}}</a></td>
                            <td class="hidden-xs">{{$roommate->esn_country}}</td>
                            <td class="hidden-xs">{{$roommate->section}}</td>
                        </tr>
                        <?php $index ++?>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div style="text-align: center;;margin-bottom: auto">
            <h3>Rooming Platform</h3>
            <p>You have successfully joined a <b><u>{{$room->beds}}-bed</u></b> room.</p>
            @if(!$room->final)
                <h3 style="color: red">Your room is not yet final!</h3>
            @endif
            <p>The room codes are:</p>

            <h3>Room ID: <b style="color: red;font-size: 130%;"> {{$room->id}}</b></h3>
            <h3>Room Code: <b style="color: red;font-size: 130%;"> {{$room->code}}</b></h3>
            <p><a class="btn btn-warning" href="{{route('participant.rooming.leave')}}">Leave room</a></p>
            The current occupants of this room are:
            <div class="container">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="hidden-xs">#</th>
                        <th>Name</th>
                        <th class="hidden-xs">ESN Country</th>
                        <th class="hidden-xs">ESN Section</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $index = 1?>
                    @foreach($roommates as $roommate)
                        <tr>
                            <td class="hidden-xs">{{$index}}</td>
                            <td><a href="#"> {{$roommate->name.' '.$roommate->surname}}</a></td>
                            <td class="hidden-xs">{{$roommate->esn_country}}</td>
                            <td class="hidden-xs">{{$roommate->section}}</td>
                        </tr>
                        <?php $index ++?>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@section('js')
    <script>
        $(document).ready($(function () {
            $('#example2').DataTable({
                'paging': false,
                'lengthChange': true,
                'searching': false,
                'ordering': true,
                'info': true,
                'autoWidth': true
            })
        }));
    </script>
@endsection