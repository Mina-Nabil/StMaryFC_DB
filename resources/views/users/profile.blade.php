@extends('layouts.app')


@section('content')

<div class="row">
    <!-- Column -->
    <div class="col-lg-4 col-xlg-3 col-md-5">
        <div class="card"> <img class="card-img" src="{{  (isset($user->mainImage->USIM_URL)) ? asset( 'storage/'. $user->mainImage->USIM_URL ) : asset('assets/images/users/def-user.png')}}" alt="Card image">
        </div>
    </div>
    <!-- Column -->
    <!-- Column -->
    <div class="col-lg-8 col-xlg-9 col-md-7">
        <div class="card">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs profile-tab" role="tablist">
                <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#profile" role="tab">User Info</a> </li>
                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#attendance" role="tab">Attendance</a> </li>
                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#images" role="tab">Images</a> </li>
                <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#settings" role="tab">Settings</a> </li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <!--second tab-->
                <div class="tab-pane active" id="profile" role="tabpanel">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-xs-6 b-r"> <strong>Model Name</strong>
                                <br>
                                <p class="text-muted">{{$user->USER_NAME}}</p>
                            </div>
                            <div class="col-md-3 col-xs-6 b-r"> <strong>Class Name</strong>
                                <br>
                                <p class="text-muted">{{$user->USER_CLASS_NAME ?? ''}}</p>
                            </div>
                            <div class="col-md-3 col-xs-6"> <strong>Birthdate</strong>
                                <br>
                                <p class="text-muted">{{$user->USER_BDAY->format('d-M-Y')}}</p>
                            </div>
                        </div>
                        <hr>
                        <strong>Account Type</strong>
                        <p class="m-t-30">{{$user->type->USTP_NAME}}</p>
                        <hr>
                        <strong>Email</strong>
                        <p class="m-t-30">{{$user->USER_MAIL ?? ''}}</p>
                    </div>
                </div>

                <div class="tab-pane" id="attendance" role="tabpanel">
                    <div class="card-body">
                        <h4 class="card-title">Take Attendance</h4>
                        <form class="form pt-3" method="post" action="{{ url($attendanceFormURL) }}" enctype="multipart/form-data">
                            @csrf
                            <input type=hidden name=userID value="{{(isset($user)) ? $user->id : ''}}">

                            <div class="form-group">
                                <label>Attendance Date</label>
                                <div class="input-group mb-3">
                                    <input type="date" value="{{ now()->format('Y-m-d')}}" class="form-control" placeholder="Pick a date" name=date required />
                                </div>
                                <small class="text-danger">{{$errors->first('date')}}</small>
                            </div>
                            <button type="submit" class="btn btn-success mr-2">Submit</button>
                        </form>
                        <hr>
                        <x-datatable id="myTable" :title="$title" :subtitle="$subTitle" :cols="$cols" :items="$items" :atts="$atts" />
                    </div>
                </div>

                <div class="tab-pane" id="images" role="tabpanel">
                    <div class="card-body">

                        <div id="carouselExampleIndicators2" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <?php $i=0; ?>
                                @foreach($user->images as $image)
                                <li data-target="#carouselExampleIndicators2" data-slide-to="{{$i}}" {{($i==0) ? 'class="active"' : ''}}></li>
                                <?php $i++; ?>
                                @endforeach
                            </ol>
                            <div class="carousel-inner" role="listbox">
                                <?php $i=0; ?>
                                @foreach($user->images as $image)
                                <div class="carousel-item {{($i==0) ? 'active' : ''}}">
                                    <img class="img-fluid" src="{{ asset( 'storage/'. $image->USIM_URL ) }} " style="max-height:350; max-width:300; height:auto; width:auto;">
                                </div>
                                <?php $i++; ?>
                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleIndicators2" role="button" data-slide="prev" style="background-color:#DCDCDC">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleIndicators2" role="button" data-slide="next" style="background-color:#DCDCDC">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                        <hr>
                        <h4 class="card-title">Add New Model Image</h4>
                        <form class="form pt-3" method="post" action="{{ url($imageFormURL) }}" enctype="multipart/form-data">
                            @csrf
                            <input type=hidden name=userID value="{{(isset($user)) ? $user->id : ''}}">

                            <div class="form-group">
                                <label for="input-file-now-custom-1">New Photo</label>
                                <div class="input-group mb-3">
                                    <input type="file" id="input-file-now-custom-1" name=photo class="dropify" data-default-file="{{ old('photo') }}" />
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success mr-2">Submit</button>
                            @if($isCancel)
                            <a href="{{url($homeURL) }}" class="btn btn-dark">Cancel</a>
                            @endif
                        </form>
                    </div>


                    <hr>
                    <div>
                        <div class=col>
                            <h4 class="card-title">All Images</h4>
                            <div class="table-responsive m-t-40">
                                <table class="table color-bordered-table table-striped full-color-table full-primary-table hover-table" data-display-length='-1' data-order="[]">
                                    <thead>
                                        <th>Url</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->images as $image)
                                        <tr>
                                            <td><a target="_blank" href="{{ asset( 'storage/'. $image->USIM_URL ) }}">
                                                    {{(strlen($image->USIM_URL) < 25) ? $image->USIM_URL : substr($image->USIM_URL, 0, 25).'..' }}
                                                </a></td>
                                            <td>
                                                @if($image->id != $user->USER_MAIN_IMGE)
                                                <a href="javascript:void(0);">
                                                    <div class="label label-info" onclick="confirmAndGoTo('{{url('users/setimage/'.$user->id.'/'.$image->id)}}', 'set this as the main Model Image')">
                                                        Set As Main </div>
                                                </a>
                                                @else
                                                <a href="javascript:void(0);">
                                                    <div class="label label-danger">Main Image</div>
                                                </a>
                                                @endif
                                            </td>
                                        <tr>
                                            @endforeach
                                    </tbody>
                                </table>
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
                                <input type=hidden name=id value="{{(isset($user)) ? $user->id : ''}}">
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
                                        <input type="date" value="{{$user->USER_BDAY->format('Y-m-d') ?? now()->format('Y-m-d')}}" class="form-control" placeholder="Pick a date" name=birthDate
                                            required />
                                    </div>
                                    <small class="text-danger">{{$errors->first('birthDate')}}</small>
                                </div>


                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon22"><i class="mdi mdi-cellphone-iphone"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name=mail placeholder="Admin Email Address" value="{{ (isset($user)) ? $user->USER_MAIL : old('mail')}}">
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Password*</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon33"><i class="ti-lock"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name=password placeholder="Password" aria-label="Password" aria-describedby="basic-addon33" @if($isPassNeeded) required
                                            @endif>
                                        <small class="text-danger">{{$errors->first('password')}}</small>

                                    </div>
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
        </div>
    </div>
    <!-- Column -->
</div>
@endsection