@extends('layouts.oc.master')

@section('content')
    <div style="text-align: center;margin-bottom: auto">
        <h3>Room {{$room->actual}}</h3>
        <p>The current occupants of the room are:</p>
        <div class="container">
            <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th class="hidden-xs">#</th>
                    <th>Name</th>
                    <th>ESN Country</th>
                    <th class="hidden-xs">ESN Section</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $index = 1;
                ?>
                @foreach($roommates as $roommate)
                    <tr>
                        <td class="hidden-xs">{{$index}}</td>
                        <td><i class="fa fa-key"></i><a href="{{route('oc.user.show',$roommate->id)}}"> {{$roommate->name.' '.$roommate->surname}}</a></td>
                        <td class="hidden-xs">{{$roommate->esn_country}}</td>
                        <td class="hidden-xs">{{$roommate->section}}</td>
                        </td>
                    </tr>
                    <?php $index++?>
                @endforeach
                </tbody>
            </table>
            <div style="text-align: center"><a class="btn btn-danger" href="{{route('oc.rooming')}}">Back</a></div>
        </div>
    </div>
@endsection