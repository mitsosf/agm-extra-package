@extends('layouts.participant.master')

@section('content')
    <h4>Fee payment</h4>
    <div class="container" style="text-align: center;font-family: 'Lato'">
        <div class="row" style="padding-top: 3%">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Participant details</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <tr>
                                <td><b>Name:</b></td>
                                <td>{{$user->name.' '.$user->surname}}</td>
                            </tr>
                            <tr>
                                <td><b>Section:</b></td>
                                <td>{{$user->section}}</td>
                            </tr>
                            <tr>
                                <td><b>Country</b></td>
                                <td>{{$user->esn_country}}</td>
                            </tr>
                            <tr>
                                <td><b>Status</b></td>
                                <td>
                                    @switch($user->spot_status)
                                        @case('approved')
                                        <b style="color: orange">Payment pending</b>

                                        @break

                                        @case('paid')
                                        <b style="color: green">Paid <a target="_blank" href="{{route('participant.generateProof')}}">(Proof)</a></b>
                                        @break

                                        @default
                                        <b style="color: red">Spot approval pending</b>
                                    @endswitch
                                </td>
                            </tr>
                            @if($user->spot_status === 'approved')
                                <tr>
                                    <td><b></b></td>
                                    <td>
                                        <div style="text-align: left">
                                            <form class="payment-card-form" method="POST" action="{{route('participant.validateCard')}}">
                                                <script type="text/javascript" class="everypay-script"
                                                        src="https://button.everypay.gr/js/button.js"
                                                        data-key="{{env('EVERYPAY_PUBLIC_KEY')}}"
                                                        data-amount="{{env('EVENT_FEE','16000')}}"
                                                        data-locale="en"
                                                        data-description="{{Auth::user()->name.' '.Auth::user()->surname}} - AGM Extra Package - Participation fee"
                                                        @if(env('APP_ENV','production') === 'local')
                                                        data-sandbox="1"
                                                        @endif
                                                >
                                                </script>
                                                @csrf
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
            <!-- /.box -->
            <div class="col-md-4">
            </div>
        </div>
        <div style="background: rgba(34,0,171,0.27); margin-right: 30%;margin-left: 30%;">

        </div>
        <div class="row">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
            <div id="loading-img"></div>
        </div>
        @if($error)
            <h3 style="color: red">{{$error}}</h3>
        @endif
    </div>
@endsection

@section('css')
    <style>
        #loading-img {
            background: url("https://i.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.webp") center center no-repeat;
            display: none;
            height: 50px;
            width: 50px;
            position: absolute;
            top: 33%;
            left: 1%;
            right: 1%;
            margin: auto;
        }
    </style>
@endsection

@section('js')
    <script>
        $(".everypay-button").click(function () {
            $("#loading-img").css({"display": "block"});
        });
    </script>
@endsection