@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $formTitle }}</h4>
                <form class="form pt-3" method="post" action="{{ url($formURL) }}" enctype="multipart/form-data">
                    @csrf
                    <input type=hidden name=id value="{{(isset($user)) ? $user->id : ''}}">
                    @if(isset($user->DASH_IMGE))
                    <input type=hidden name=oldPath value="{{$user->DASH_IMGE}}">
                    @endif

                    <div class="form-group">
                        <label for="exampleInputEmail1">ID</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon22"><i class="mdi mdi-barcode"></i></span>
                            </div>
                            <input type="text" class="form-control" name=code placeholder="User ID" value="{{ (isset($user)) ? $user->USER_CODE : old('code')}}">
                        </div>
                        <small class="text-danger">{{$errors->first('code')}}</small>

                    </div>

                    <div class="form-group">
                        <label>User Name*</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon11"><i class="ti-user"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Username" name=name value="{{ (isset($user)) ? $user->USER_NAME : old('name')}}" required>
                        </div>
                        <small class="text-danger">{{$errors->first('name')}}</small>
                    </div>
                    <div class="form-group">
                        <label>Class*</label>
                        <div class="input-group mb-3">
                            <select name=group class="select2 form-control custom-select" style="width: 100%; height:36px;" required>
                                <option value="" disabled selected>Pick From Available Classes</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" @if(isset($user) && $group->id == $user->USER_GRUP_ID)
                                    selected
                                    @elseif($group->id == old('group'))
                                    selected
                                    @endif
                                    >{{$group->GRUP_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('group')}}</small>
                    </div>

                    <div class="form-group">
                        <label>User Type*</label>
                        <div class="input-group mb-3">
                            <select name=type class="select2 form-control custom-select" style="width: 100%; height:36px;" required>
                                <option value="" disabled selected>Pick From Users Types</option>
                                @foreach($types as $type)
                                <option value="{{ $type->id }}" @if(isset($user) && $type->id == $user->USER_USTP_ID)
                                    selected
                                    @elseif($type->id == old('type'))
                                    selected
                                    @endif
                                    >{{$type->USTP_NAME}}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-danger">{{$errors->first('type')}}</small>
                    </div>

                    <div class="form-group">
                        <label>Birth Date</label>
                        <div class="input-group mb-3">
                            <input type="date" value="{{$user->USER_BDAY ?? now()->format('Y-m-d')}}" class="form-control" placeholder="Pick a date" name=birthDate required />
                        </div>
                        <small class="text-danger">{{$errors->first('birthDate')}}</small>
                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Phone Number</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon22"><i class="mdi mdi-cellphone-iphone"></i></span>
                            </div>
                            <input type="number" class="form-control" name=mobn placeholder="Phone Number" value="{{ (isset($user)) ? $user->USER_MOBN : old('mobn')}}">
                        </div>
                        <small class="text-danger">{{$errors->first('mobn')}}</small>

                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Admin MobApp Username</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon22"><i class="mdi mdi-email"></i></span>
                            </div>
                            <input type="text" class="form-control" name=mail placeholder="Username" value="{{ (isset($user)) ? $user->USER_MAIL : old('mail')}}">
                        </div>
                        <small class="text-danger">{{$errors->first('mail') != "" ? $errors->first('mail') : "Required if type is admin"}}</small>

                    </div>


                    <div class="form-group">
                        <label>Admin MobApp Password</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon33"><i class="ti-lock"></i></span>
                            </div>
                            <input type="text" class="form-control" name=password placeholder="Password" aria-label="Password" aria-describedby="basic-addon33" @if($isPassNeeded) required @endif>
                        </div>
                        <small class="text-danger">{{$errors->first('password') != "" ? $errors->first('password') : "Required if type is admin"}}</small>

                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Comment</label>
                        <div class="input-group mb-3">
                            <textarea class="form-control" name=note>{{ (isset($user)) ? $user->USER_NOTE : old('note')}}</textarea>
                        </div>
                        <small class="text-danger">{{$errors->first('note')}}</small>
                    </div>

                    <button type="submit" class="btn btn-success mr-2">Submit</button>
                    @if($isCancel)
                    <a href="{{url($homeURL) }}" class="btn btn-dark">Cancel</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection