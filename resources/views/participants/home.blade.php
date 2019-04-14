@extends('layouts.participant.master')

@section('content')
    @if(Session::get('paid_fee') == 1)
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">You have successfully paid the event fee!!</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                Get ready for the greatest event of the year!
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    @endif
    <div class="row">
        @if($payments || Auth::user()->spot_status == 'paid')
            <div class="col-md-3 col-sm-6 col-xs-12">
                <!-- small box -->
                <a href="{{route('participant.payment')}}">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>Fee</h3>
                            @if($user->spot_status === 'paid')
                                <p>You have successfully paid the fee</p>
                            @else
                                <p>Pay Thessaloniki Castaways participation fee</p>
                            @endif
                        </div>
                        <div class="icon">
                            <i class="fa fa-eur"></i>
                        </div>
                        <div class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></div>
                    </div>
                </a>
                @elseif(Auth::user()->isAlumni() && env('EVENT_PAYMENTS',0))
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <a href="{{route('participant.payment')}}">
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3>Fee</h3>
                                    @if($user->spot_status === 'paid')
                                        <p>You have successfully paid the fee</p>
                                    @else
                                        <p>Pay Thessaloniki Castaways participation fee</p>
                                    @endif
                                </div>
                                <div class="icon">
                                    <i class="fa fa-eur"></i>
                                </div>
                                <div class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></div>
                            </div>
                        </a>
                        @else
                            <div class="col-md-4"></div>
                            <div class="col-md-4" style="text-align: center">
                                <img style="max-width: 80%" src="{{asset("images/sorry_castaways.jpg")}}" alt="Sorry">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <a href="{{route('participant.rooming')}}">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>Rooming</h3>
                                    <p>Find roommates</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-hotel"></i>
                                </div>
                                <div class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></div>
                            </div>
                        </a>
                    </div>
                    @if(env('EVENT_DEPOSITS',0) == 1)
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <!-- small box -->
                            <a href="{{route('participant.deposit')}}">
                                <div class="small-box bg-yellow">
                                    <div class="inner">
                                        <h3>Deposit</h3>
                                        @if($user->spot_status === 'paid')
                                            <p>You have successfully paid the deposit</p>
                                        @else
                                            <p>Pay the event deposit</p>
                                        @endif
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-lock"></i>
                                    </div>
                                    <div class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></div>
                                </div>
                            </a>
                        </div>
                    @endif
            </div>
@endsection