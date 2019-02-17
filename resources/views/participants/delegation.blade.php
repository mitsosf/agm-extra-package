@extends('layouts.participant.master')

@section('content')
    <div class="container">
        <h4>My Castaways:</h4>
        <div class="box-body" style="background: white">
            <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th class="hidden-xs">Section</th>
                    <th>Paid</th>
                    {{-- <th class="hidden-xs">Room</th>
                     <th class="hidden-xs">Check-in</th>--}}
                </tr>
                </thead>
                <tbody>
                @foreach($participants as $user)
                    @php
                        $transaction = $user->transactions->where('type','fee')->first();
                        $debt = $user->transactions->where('type', 'debt')->first();
                    @endphp
                    <tr>
                        <td>{{$user->name." ".$user->surname}}</td>
                        <td><a href="tel:{{$user->phone}}">{{$user->phone}}</a></td>
                        <td class="hidden-xs">{{$user->section}}</td>
                        @if(isset($transaction))
                            <td style="text-align: center"><span class="label label-success">Yes</span></td>
                        @else
                            <td style="text-align: center"><span class="label label-danger">No</span></td>
                        @endif
                        {{--@if($user->rooming == 0)
                            <td style="text-align: center" class="hidden-xs"><span class="label label-danger">No</span></td>
                        @else
                            <td style="text-align: center" class="hidden-xs"><span class="label label-success">{{$user->rooming}}</span></td>
                        @endif
                        @if($user->checkin == 0)
                            <td style="text-align: center" class="hidden-xs"><span class="label label-danger">No</span></td>
                        @else
                            <td style="text-align: center" class="hidden-xs"><span class="label label-success">Yes</span></td>
                        @endif--}}
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th class="hidden-xs">Section</th>
                    <th>Paid</th>
                    {{-- <th class="hidden-xs">Room</th>
                     <th class="hidden-xs">Check-in</th>--}}
                </tr>
                </tfoot>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    </div>
@endsection

@section('js')
    <script>
        $(document).ready($(function () {
            $('#example2').DataTable({
                'paging': true,
                'lengthChange': true,
                'searching': true,
                'ordering': true,
                'info': true,
                'autoWidth': true
            })
        }));
        $(document).ready($(function focusOnSearch() {
            $('div.dataTables_filter input').focus();
        }));
    </script>
@endsection