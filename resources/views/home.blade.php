@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Quick Action Buttons</div>

                <div class="card-body">
                    <div class=row>
                        <div class=col-6>
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="d-flex justify-content-end align-items-center">
                                    <a style="font-family: 'Oswald'" href="{{url('users/show')}}" class="btn btn-dark d-lg-block m-15"><i class="fa fa-info-circle"></i> Check
                                        Attendance</a>
                                </div>


                            </div>
                        </div>
                        <div class=col-6>
                            <div class="d-flex justify-content-end align-items-center">
                                <a style="font-family: 'Oswald'" href="{{url('payments/add')}}" class="btn btn-dark d-lg-block m-15"><i class="fa fa-plus-circle"></i> Add Payment</a>
                            </div>
                        </div>
                    </div>
                    <div class=row>
                        <div class=col-6>
                            <div class="d-flex justify-content-end align-items-center">
                                <a style="font-family: 'Oswald'" href="{{url('users/add')}}" class="btn btn-dark d-lg-block m-15"><i class="fa fa-plus-circle"></i> Add App User</a>
                            </div>
                        </div>
                        <div class=col-6>
                            <div class="d-flex justify-content-end align-items-center">
                                <a style="font-family: 'Oswald'" href="{{url('attendance/add')}}" class="btn btn-dark  d-lg-block m-15"><i class="fa fa-plus-circle"></i> Manual Attendance
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection