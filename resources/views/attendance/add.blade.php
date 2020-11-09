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
                            <select name=userID[] class="select2 select2-multiple"  multiple="multiple" data-placeholder="Choose From The Following Users" style="width: 100%; height:36px;" required>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{$user->group->GRUP_NAME}}-{{$user->USER_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('userID')}}</small>
                    </div>

                    <div class="form-group">
                        <label>Attendace Date</label>
                        <div class="input-group mb-3">
                            <input type="date" value="{{ now()->format('Y-m-d')}}" class="form-control" placeholder="Pick a date" name=date required />
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