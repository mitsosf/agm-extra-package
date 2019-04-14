@extends('layouts.oc.master')

@section('content')
    <h3 style="text-align: center">Rooming</h3>
    <div class="box">
        <div class="row">
            <div class="col-sm-4">
                <div class="box-header">
                    <h3 class="box-title" style="text-align: center">Rooms</h3>
                </div>
                <!-- /.box-header -->
            </div>
        </div>
        <div class="box-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Hotel</th>
                    @foreach($availableBeds as $key=>$availableBed)
                        <th>{{$key}}-beds</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Stay Hostel</td>
                    @foreach($availableBeds as $key=>$availableBed)
                        <td>{{$occupiedBeds[$key].'/'.$availableBed}}</td>
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <!-- /.box -->
    </div>

    <div class="box">
        <div class="row">
            <div class="col-sm-4">
                <div class="box-header">
                    <h3 class="box-title" style="text-align: center">Rooms</h3>
                </div>
                <!-- /.box-header -->
            </div>
        </div>
        <div class="box-body">
            <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th class="hidden-xs">Final</th>
                    <th>ID</th>
                    <th>Actual</th>
                    <th class="hidden-xs">Hotel</th>
                    <th>Beds</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rooms as $room)
                    <tr>
                        <td>
                            <div style="text-align: center">{{$room->final == 1 ? "Final": "Open"}}</div>
                        </td>
                        <td style="width: 20%;text-align: center"><a href="{{route('oc.room.show',$room)}}">Room {{$room->id}}</a>
                        </td>
                        <td>
                            <div style="text-align: center">#</div>
                        </td>
                        <td>
                            <div style="text-align: center">{{$room->hotel->name}}</div>
                        </td>
                        <td>
                            <div style="text-align: center">{{$room->beds}}</div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th class="hidden-xs">Final</th>
                    <th>ID</th>
                    <th>Actual</th>
                    <th class="hidden-xs">Hotel</th>
                    <th>Beds</th>
                </tr>
                </tfoot>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
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