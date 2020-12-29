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
                        <label>Date*</label>
                        <div class="input-group mb-3">
                            <input type="date" class="form-control" placeholder="Pick a date" name=date required />
                        </div>
                        <small class="text-danger">{{$errors->first('date')}}</small>
                    </div>

                    <div class="form-group">
                        <label>Name*</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Event Name" name=name required />
                        </div>
                        <small class="text-danger">{{$errors->first('name')}}</small>
                    </div>

                    <div class="form-group">
                        <label>Price*</label>
                        <div class="input-group mb-3">
                            <input type="number" step=0.01  class="form-control" placeholder="Event Price Per Player" name=price required />
                        </div>
                        <small class="text-danger">{{$errors->first('price')}}</small>
                    </div>

                    <div class="form-group">
                        <label>Event Note</label>
                        <div class="input-group mb-3">
                            <textarea class="form-control" name=comment></textarea>
                        </div>
                        <small class="text-danger">{{$errors->first('comment')}}</small>
                    </div>

                    <button type="submit" class="btn btn-success mr-2">Submit</button>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
