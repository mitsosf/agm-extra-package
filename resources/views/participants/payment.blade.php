@extends('layouts.participant.master')

@section('content')
    <h4>Fee payment</h4>
    <div class="container" style="text-align: center;font-family: 'Lato'">
        <div class="row" style="padding-top: 3%">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="box" style="position: relative">
                    <img style="max-width: 70%;display: none"
                         src="{{asset('images/loading.gif')}}" id="loading"
                         alt="Loading">
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
                                        <b style="color: green">Paid <a target="_blank"
                                                                        href="{{route('participant.generateProof')}}">(Proof)</a></b>
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
                                        <div style="text-align: left;position: relative">
                                            <form class="payment-card-form" method="POST"
                                                  action="{{route('participant.validateCard')}}">
                                                <script type="text/javascript" class="everypay-script"
                                                        src="https://button.everypay.gr/js/button.js"
                                                        data-key="{{env('EVERYPAY_PUBLIC_KEY')}}"
                                                        data-amount="{{env('EVENT_FEE','16200')}}"
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

        @if($error)
            <h3 style="color: red">{{$error}}</h3>
        @endif
        @if($user->spot_status === 'approved')
            <div class="row">
                <h5 id="warning" style="color: red;">Notice: When you enter your card details and pay the fee, please do
                    not close
                    the window and wait for some seconds :)</h5>
            </div>
        @endif
    </div>
@endsection

@section('js')
    <script>
        $(document).delay(2000).ready(function () {
            $('.payment-card-form').click(function () {
                $('.table.table-striped').hide();
                $('.box-header').hide();
                $('#warning').text('Please don\'t close the window until the payment is approved');
                $('#loading').show();
            });
        });

        setInterval(() => {
            $('.everypay-3d-secure-close-btn').click(function () {
                window.location.replace('{{env('APP_URL')}}');
            })
        }, 500);

    </script>
@endsection