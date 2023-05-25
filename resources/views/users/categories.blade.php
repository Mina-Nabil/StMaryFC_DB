@extends('layouts.app')

@section('content')

<div class=row>
    <div class="col-lg-6 col-12">
        <div class="card">
            <div class="card-header bg-dark">
                <h4 class="m-b-0 text-white">Payment Categories</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    @csrf
                    <div class=row>
                        @isset($category)
                        <input type="hidden" class="form-control" id=categoryID value="{{$category->id}}">
                        @endisset
                        <div class=col-12>
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Name*" name=title @isset($category) value="{{$category->title}}" @endisset required>
                                </div>
                                <small class="text-danger">{{$errors->first('title')}}</small>
                            </div>
                        </div>


                    </div>
                    <div class=row>
                        <div class="col-2 m-r-10">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                        @isset($category)
                        <div class="col-2 m-r-10">
                            <button type="button" onclick="goToCategory()" class="btn btn-dark">Cancel</button>
                        </div>
                        <div class="col-2 m-r-10">
                            <button type="button" onclick="confirmAndGoTo('{{url('users/categories/'.$category->id.'/delete' )}}', 'delete this category ?')" class="btn btn-danger mr-2">Delete</button>
                        </div>
                        @endisset
                    </div>
                </form>
                <hr>
                <label>Categories</label>
                <ul class="list-group" id=pricelistList>
                    @foreach($categories as $catg)
                    <a id="catg{{$catg->id}}" href="javascript:void(0)" onclick="goToCategory({{$catg->id}})" class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1 text-dark" id="pricelistName{{$catg->id}}">{{$catg->title}}</h5>
                        </div>
                    </a>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @isset($category)
    <div class="col-lg-6 col-12" id="itemsDiv">
        <div class="card">
            <div class="card-header bg-dark">
                <h4 class="m-b-0 text-white">Category details</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{url('users/categories/details')}}">
                    @csrf
                    <input id=selectedPricelistID name=category_id type="hidden" value="{{$category->id}}">
                    <div id="itemsContainer">
                        @foreach($category->details as $detaila)
                        <div class="row removeclass{{$loop->index}}">
                            <div class=col-6>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Attendance count" name=attendance[] value="{{$detaila->attendance}}" required>
                                    </div>
                                </div>
                            </div>
                            <div class=col-6>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Due" name=due[] value="{{$detaila->payment}}" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-danger" type="button" onclick="removeDetailRow({{$loop->index}});"><i class="fa fa-minus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="col-12 m-r-10">
                        <button class="btn btn-dark" type="button" onclick="addDetailRow();"><i class="fa fa-plus"></i></button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endisset
</div>
@endsection

@section('js_content')

<script>
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

    function goToCategory(id){
        if(id == null)   window.location='{{url("users/categories")}}' 
        else
        window.location='{{url("users/categories")}}' + '/' + id
    }

    var room = @isset($category) {{$category->details->count()}} @else 0 @endisset

    function addDetailRow()
    {
        var objTo = document.getElementById('itemsContainer')
        var divtest = document.createElement("div");
        divtest.setAttribute("class", "nopadding row col-lg-12 removeclass" + room);
        var rdiv = 'removeItem' + room;
        var concatString = ` <div class=row>
                            <div class=col-6>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" placeholder="Attendance count" 
                                        name=attendance[]  required>
                                    </div>
                                </div>
                            </div>
                            <div class=col-6>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" placeholder="Due" name=due[] required>
                                        <div class="input-group-append">
                                            <button class="btn btn-danger" type="button"
                                             onclick="removeDetailRow(` + room++ + `);"><i class="fa fa-minus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;

        divtest.innerHTML = concatString;
        
        objTo.appendChild(divtest);

    }
    function removeDetailRow(rid) {
        $('.removeclass' + rid).remove();
    }

</script>

@endsection