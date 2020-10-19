@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $formTitle }}</h4>
                <form class="form pt-3" method="post" action="{{ url($formURL) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>User*</label>
                        <div class="input-group mb-3">
                            <select name=userID class="select2 form-control custom-select" style="width: 100%; height:36px;" id=userSel onchange="getDueDays(userSel)" required>
                                <option value="" disabled selected>Pick From Registered Users</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{$user->USER_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('userID')}}</small>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount</label>
                        <div class="input-group mb-3">
                            <input type="number" step=0.01  class="form-control" placeholder="Recieved Value" name=amount required />
                        </div>
                        <small class="text-danger">{{$errors->first('amount')}}</small>
                    </div>
                    <div class="form-group">
                        <label>Payment For</label>
                        <div class="input-group mb-3">
                            <input type="month" value="{{ now()->format('Y-m')}}" class="form-control" placeholder="Pick a date" name=date required />
                        </div>
                        <small class="text-danger">{{$errors->first('date')}}</small>
                    </div>
                    <div class="form-group">
                        <label>Covering Days</label>
                        <div class="input-group mb-3">
                            <select class="select2 m-b-10 select2-multiple" style="width: 100%" multiple="multiple" data-placeholder="Choose From The Following Tags" id=days name=days[]>
                                <option value="0">All Previous days</option>
                            </select>
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Payment Note</label>
                        <div class="input-group mb-3">
                            <textarea class="form-control" name=note></textarea>
                        </div>
                        <small class="text-danger">{{$errors->first('date')}}</small>
                    </div>

                    <button type="submit" class="btn btn-success mr-2">Submit</button>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_content')
<script>
    function getDueDays(callerID) {
    userID = callerID.options[callerID.selectedIndex].value;
    var http = new XMLHttpRequest();
    var url = "{{url('payments/get/unpaid/')}}" + '/' +  userID;
    http.open('GET', url, true);
    //Send the proper header information along with the request
    //http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');


    http.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        try {        
            prices = JSON.parse(this.responseText);
            listtt = document.getElementById('days' );
            listtt.innerHTML = '';
            listtt.innerHTML += '<option value="0">All Month</option>';
            prices.forEach(element => {
                listtt.innerHTML += '<option value="' + element['id'] + '">' + element['date'] + '</option>';
            });
        } catch(e){
            console.log(e); 
        }
      } 
    };
    http.send();
   }

</script>
@endsection