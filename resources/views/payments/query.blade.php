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
                            <select name=userID class="select2 form-control custom-select" style="width: 100%; height:36px;" required>
                                <option value="" disabled selected>Pick From Registered Users</option>
                                <option value="0">All Users</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{$user->USER_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('userID')}}</small>
                    </div>

                    <div class="form-group">
                        <div class="bt-switch">
                            <div>
                                <input type="checkbox" data-size="large" data-on-color="info" data-off-color="success" data-on-text="Date" name="isDate" data-off-text="Due">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>From</label>
                        <div class="input-group mb-3">
                            <input type="date" value="{{ now()->format('Y-m-01')}}" class="form-control" placeholder="Pick a date" name=fromDate required />
                        </div>
                        <small class="text-danger">{{$errors->first('fromDate')}}</small>
                    </div>

                    <div class="form-group">
                        <label>To</label>
                        <div class="input-group mb-3">
                            <input type="date" value="{{ now()->format('Y-m-d')}}" class="form-control" placeholder="Pick a date" name=toDate required />
                        </div>
                        <small class="text-danger">{{$errors->first('toDate')}}</small>
                    </div>

                    <button type="submit" class="btn btn-success mr-2">Submit</button>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection