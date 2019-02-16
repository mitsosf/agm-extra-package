@extends('layouts.oc.master')

@section('content')
    <h4>Dashboard</h4>
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <!-- small box -->
            <a href="{{route('oc.approved')}}">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{$paidUsersCount}}</h3>

                        <p> ({{round(($paidUsersCount/$totalUsers)*100,2)}}%)</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <!-- small box -->
            <a href="{{route('oc.cashflow')}}">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{$funds}} €</h3>
                        <p>Wow, we're rich</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-eur"></i>
                    </div>
                    <div class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></div>
                </div>
            </a>
        </div>
        {{--<div class="col-md-3 col-sm-6 col-xs-12">
            <!-- small box -->
            <a href="#">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{$roomedUsers}}</h3>

                        <p>Rooms ({{$approvedUsers?round(($roomedUsers/$approvedUsers)*100,2):'0'}}%)</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bar-chart"></i>
                    </div>
                    <div class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></div>
                </div>
            </a>
        </div>--}}
    </div>
@endsection