@extends('layouts.app')


@section('content')

<script>
    function loadEvent(){
        eventID = document.getElementById('eventLoader').value;
        window.location.href = '{{url("events/")}}' + eventID
    }
</script>

<div class="row">
    <!-- Column -->
    <div class="col-12">
        <div class=card>
            <div class=card-body>
                <label>Load Event</label>
                <div class=row>
                    <div class=col-9>
                        <div class="input-group mb-3">
                            <select class="select2 m-b-10 " style="width: 100%" id=eventLoader>
                                @foreach ($events as $row)
                                <option value="{{$row->id}}" {{($row->id == $event->id) ? 'selected' : ''}}>{{$row->EVNT_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class=col-3>
                        <button onclick="loadEvent()" class="btn btn-success waves-effect waves-light">Load</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Column -->
    <!-- Column -->
    <div class="col-12">
        <div class="card">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs profile-tab" role="tablist">
                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#attendance" role="tab">Attendance</a> </li>
                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#settings" role="tab">Event Data</a> </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <!--second tab-->


                <div class="tab-pane active" id="attendance" role="tabpanel">
                    <div class="card-body">
                        <div class="card-body">
                            <div class="table-responsive m-t-5">
                                <table id="itemsTable" class="table color-bordered-table table-striped full-color-table full-info-table hover-table" data-display-length='-1' data-order="[]">
                                    <thead>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Paid</th>
                                        <th>Actions</th>
                                    </thead>
                                    <tbody>
                                        @foreach($attendance as $item)

                                        <tr id="item{{$item->EVNT_ID}}-{{$item->USER_ID}}">

                                            <td>{{$item->GRUP_NAME}}: {{$item->USER_NAME}}</td>
                                            <td id="status{{$item->EVNT_ID}}-{{$item->USER_ID}}">
                                                @switch($item->EVAT_STTS)
                                                @case(1)
                                                <span class="label label-info">Reserved</span>
                                                @break
                                                @case(2)
                                                <span class="label label-success">Attended</span>
                                                @break
                                                @case(3)
                                                <span class="label label-warning">Cancelled</span>
                                                @break
                                                @default
                                                <span class="label label-inverse">Missed</span>
                                                @endswitch
                                            </td>
                                            <td>{{$item->EVPY_PAID}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button style="padding:.1rem .2rem" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        Action
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <button class="dropdown-item" data-toggle="modal" data-id="{{$item->USER_ID}}%%{{$item->EVNT_ID}}" data-target="#open-status">Status</button>
                                                        <button class="dropdown-item" data-toggle="modal" data-id="{{$item->USER_ID}}%%{{$item->EVNT_ID}}" data-target="#open-pay">Pay</button>
                                                        <button class="dropdown-item" onclick="deleteAttendance(data-id='{{$item->USER_ID}}','{{$item->EVNT_ID}}')">Remove</button>
                                                    </div>

                                                </div>
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pay" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Payment</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form action="{{ url('orders/change/quantity') }}" method=post>
                                @csrf
                                <div class="modal-body">
                                    <input type=hidden name=userIDModal>
                                    <input type=hidden name=eventIDModal>

                                    <div class="form-group col-md-12 m-t-0">
                                        <h5>Amount</h5>
                                        <input type="number" step=1 class="form-control form-control-line" name=count value="" required>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-warning waves-effect waves-light">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="pay" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Payment</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form action="{{ url('orders/change/quantity') }}" method=post>
                                @csrf
                                <div class="modal-body">
                                    <input type=hidden name=userIDModal>
                                    <input type=hidden name=eventIDModal>

                                    <div class="form-group col-md-12 m-t-0">
                                        <h5>Status</h5>
                                        <select class="select2 m-b-10 " style="width: 100%" id=eventLoader>
                                            <option value="1" >Reserved</option>
                                            <option value="2" >Attended</option>
                                            <option value="3" >Cancelled</option>
                                        </select>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-warning waves-effect waves-light">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="settings" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">{{ $formTitle }}</h4>
                            <form class="form pt-3" method="post" action="{{ url($formURL) }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{$event->id}}">
                                <div class="form-group">
                                    <label>Date*</label>
                                    <div class="input-group mb-3">
                                        <input type="date" class="form-control" placeholder="Pick a date" name=date value="{{$event->EVNT_DATE}}" required />
                                    </div>
                                    <small class="text-danger">{{$errors->first('date')}}</small>
                                </div>

                                <div class="form-group">
                                    <label>Name*</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Event Name" name=name value="{{$event->EVNT_NAME}}" required />
                                    </div>
                                    <small class="text-danger">{{$errors->first('name')}}</small>
                                </div>

                                <div class="form-group">
                                    <label>Price*</label>
                                    <div class="input-group mb-3">
                                        <input type="number" step=0.01 class="form-control" placeholder="Event Price Per Player" name=price value="{{$event->EVNT_PRCE}}" required />
                                    </div>
                                    <small class="text-danger">{{$errors->first('price')}}</small>
                                </div>

                                <div class="form-group">
                                    <label>Event Note</label>
                                    <div class="input-group mb-3">
                                        <textarea class="form-control" name=comment>{{$event->EVNT_CMNT ?? ""}}</textarea>
                                    </div>
                                    <small class="text-danger">{{$errors->first('comment')}}</small>
                                </div>

                                <button type="submit" class="btn btn-success mr-2">Submit</button>

                            </form>
                            <hr>
                            <h4 class="card-title">Delete Event</h4>
                            <button type="button" onclick="confirmAndGoTo('{{url('events/delete/'.$event->id )}}', 'delete this event and all its attendance data and payments ?')"
                                class="btn btn-danger mr-2">Delete All Event Data</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_content')
<script>
    $(document).on("click", ".open-pay", function () {
  
        var idData = $(this).data('id');
        var idArr = idData.split('%%');
    
        $("#pay #userIDModal").val( idArr[0] );
        $("#pay #eventIDModal").val( idArr[1] );

    });
    $(document).on("click", ".open-status", function () {
  
        var idData = $(this).data('id');
        var idArr = idData.split('%%');

        $("#status #userIDModal").val( idArr[0] );
        $("#status #eventIDModal").val( idArr[1] );

});
</script>
@endsection