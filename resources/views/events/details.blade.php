@extends('layouts.app')


@section('content')

<script>
    function loadEvent(){
        eventID = document.getElementById('eventLoader').value;
        window.location.href = '{{url("events/")}}' + "/" + eventID
    }

    function confirmAndGoTo(url, action){
    Swal.fire({
        text: "Are you sure you want to " + action + "?",
        icon: "warning",
        showCancelButton: true,
        }).then((isConfirm) => {
    if(isConfirm.value){

    window.location.href = url;
        }
    });

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
                                <table id="itemsTable" class="table color-bordered-table table-striped full-color-table full-primary-table hover-table" data-display-length='-1' data-order="[]">
                                    <thead>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Paid</th>
                                        <th>Actions</th>
                                    </thead>
                                    <tbody>
                                        @foreach($attendance as $item)

                                        <tr id="row{{$item->USER_ID}}">

                                            <td>{{$item->GRUP_NAME}}: {{$item->USER_NAME}}</td>
                                            <td id="status{{$item->USER_ID}}">
                                                @switch($item->EVAT_STTS)
                                                @case(1)
                                                <span class="label label-info">Paid</span>
                                                @break
                                                @case(2)
                                                <span class="label label-warning">Received</span>
                                                @break
                                                @case(3)
                                                <span class="label label-success">Ok</span>
                                                @break
                                                @default
                                                <span class="label label-inverse">None</span>
                                                @endswitch
                                            </td>
                                            <td id="paid-{{$item->USER_ID}}">{{$item->EVPY_USER_AMNT ?? 0}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button style="padding:.1rem .2rem" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        Action
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <button class="open-statusModal dropdown-item" data-toggle="modal" data-id="{{$item->USER_ID}}" data-target="#statusModal">Set Status</button>
                                                        <button class=" open-payModal dropdown-item" data-toggle="modal" data-id="{{$item->USER_ID}}" data-target="#payModal">Pay</button>
                                                        <button class="dropdown-item" onclick="deletePayments('{{$item->EVNT_ID}}', '{{$item->USER_ID}}')">Delete Payments</button>
                                                        @if($item->EVAT_STTS)
                                                        <button class="dropdown-item" onclick="deleteAttendance('{{$item->USER_ID}}')">Remove</button>
                                                        @endif
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
                <div id="payModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Payment</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            @csrf
                            <div class="modal-body">
                                <input type=hidden name=userIDModal id=userIDPay>
                                <input type=hidden name=eventIDModal id=eventIDPay>

                                <div class="form-group col-md-12 m-t-0">
                                    <h5>Amount</h5>
                                    <input type="number" step=1 class="form-control form-control-line" id=amount value="" required>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                                <button type="button" onclick="addPayment()" class="btn btn-warning waves-effect waves-light">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="statusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Change Status</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>

                            @csrf
                            <div class="modal-body">
                                <input type=hidden id=userIDState>
                                <input type=hidden id=eventIDState>

                                <div class="form-group col-md-12 m-t-0">
                                    <h5>Status</h5>
                                    <select class="form-control" style="width: 100%" id=eventState>
                                        <option value="0">None</option>
                                        <option value="1">Paid</option>
                                        <option value="2">Received</option>
                                        <option value="3">Ok</option>
                                    </select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                                <button type="button" onclick="changeResState()" class="btn btn-warning waves-effect waves-light">Submit</button>
                            </div>

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
    function deleteAttendance(userID){

        var http = new XMLHttpRequest();
        var url = "{{$deleteAttendanceURL}}" ;
        var formdata = new FormData();
        var eventID = "{{$event->id}}"
        formdata.append("userID", userID);
        formdata.append("eventID", eventID);
        formdata.append('_token','{{ csrf_token() }}');
        http.open('POST', url, true);

        http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && this.responseText=="1") {
            Swal.fire({
            text: "Player Attendace set to None",
            icon: "success",
            });
            var statusLabel = document.getElementById("status" +userID )
            statusLabel.innerHTML="<span class=\"label label-inverse\">None</span>"
        } else {
            Swal.fire({
            text: "Oops Something wrong.. Please try again",
            title: "Error",
            icon: "error",
            }); 
        }
        };
        http.send(formdata);
    }

    function deletePayments(eventID, userID){

        var http = new XMLHttpRequest();
        var url = "{{$removePaymentURL}}";
        var formdata = new FormData();
        formdata.append("eventID", eventID);
        formdata.append("userID", userID);
        formdata.append('_token','{{ csrf_token() }}');
        http.open('POST', url, true);

        http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && parseInt(this.responseText)>0) {
            Swal.fire({
            text: "Player Payments deleted",
            icon: "success",
            });
            var statusLabel = document.getElementById("paid-" + userID )
            statusLabel.innerHTML="0"
        } else {
            Swal.fire({
            text: "No change :)",
            icon: "warning",
            }); 
        }
        };
        http.send(formdata);
    }

    function changeResState(){
                        
        var http = new XMLHttpRequest();
        var url = "{{$setStatusURL}}" ;

        var userID = document.getElementById('userIDState').value
        var eventID = "{{$event->id}}"
        var eventState = document.getElementById('eventState').value

        var formdata = new FormData();
        formdata.append("userID", userID);
        formdata.append("eventID", eventID);
        formdata.append("status", eventState);
        formdata.append('_token','{{ csrf_token() }}');
        http.open('POST', url, true);

        http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && this.responseText=="1") {
            Swal.fire({
            text: "Player Attendace Updated",
            icon: "success",
            });
            var statusLabel = document.getElementById("status" +userID )
            switch(eventState) {                                       
                case "1":
                    statusLabel.innerHTML="<span class=\"label label-info\">Paid</span>"
                    break;
                case "2":
                    statusLabel.innerHTML="<span class=\"label label-warning\">Received</span>"
                    break;
                case "3":
                    statusLabel.innerHTML="<span class=\"label label-success\">Ok</span>"
                    break;
                default:
                    statusLabel.innerHTML="<span class=\"label label-inverse\">None</span>"
                }
            
        }  else {
            Swal.fire({
            text: "Oops Something wrong.. Please try again",
            title: "Error",
            icon: "error",
            }); 
        }
        };
        http.send(formdata, true);      
    }


    function addPayment(){

        var http = new XMLHttpRequest();
        var url = "{{$addPaymentURL}}" ;

        var userID = document.getElementById('userIDPay').value
        var amount = document.getElementById('amount').value
        var eventID = "{{$event->id}}"

        var formdata = new FormData();
        formdata.append("userID", userID);
        formdata.append("eventID", eventID);
        formdata.append("amount", amount);
        formdata.append("type", 2);
        formdata.append('_token','{{ csrf_token() }}');
        http.open('POST', url, true);

        http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && this.responseText=="1") {
            Swal.fire({
            text: "Player Payment Added",
            icon: "success",
            });
            var paymentCell = document.getElementById("paid-" +userID )
            paymentCell.innerHTML = parseInt(paymentCell.innerHTML ?? 0) + parseInt(amount)
            
        }  else {
            Swal.fire({
            text: "Oops Something wrong.. Please try again",
            title: "Error",
            icon: "error",
            }); 
        }
        };
        http.send(formdata, true);
}


    $(document).on("click", ".open-payModal", function () {
  
        var idData = $(this).data('id');
        $("#userIDPay").val( idData );
    

    });
    $(document).on("click", ".open-statusModal", function () {
  
        var idData = $(this).data('id');
        $("#userIDState").val( idData );
  

});
</script>
@endsection