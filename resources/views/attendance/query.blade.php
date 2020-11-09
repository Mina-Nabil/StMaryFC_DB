@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $formTitle }}</h4>
                <form class="form pt-3" method="post" action="{{ url($formURL) }}" enctype="multipart/form-data">
                    @csrf
                    @if ($byGroup)
                    <div class="form-group">
                        <label>Group*</label>
                        <div class="input-group mb-3">
                            <select name=groupID class="select2 form-control custom-select" style="width: 100%; height:36px;" required>
                                <option value="" disabled selected>Pick a Group</option>
                                <option value="0">All Groups</option>
                                @foreach($items as $group)
                                <option value="{{ $group->id }}">{{$group->GRUP_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('groupID')}}</small>
                    </div>
                    @else 
                    <div class="form-group">
                        <label>User*</label>
                        <div class="input-group mb-3">
                            <select name=userID class="select2 form-control custom-select" style="width: 100%; height:36px;" required>
                                <option value="" disabled selected>Pick From Registered Users</option>
                                <option value="0">All Users</option>
                                @foreach($items as $user)
                                <option value="{{ $user->id }}">{{$user->USER_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('userID')}}</small>
                    </div>
                    @endif
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