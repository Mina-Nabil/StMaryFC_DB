@extends('layouts.app')

@section('content')
    @if ($showDueFilter)
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter by Group</h4>
                        <form class="form pt-3" method="get">
                            @csrf
                            <label>Group</label>
                            <div class="input-group mb-3">
                                <select class="form-control select m-b-10 " style="width: 100%" name=group_id >
                                    <option value="0" @selected($selected_group == 0)>All</option>
                                    @foreach ($groups as $g)
                                    <option value="{{$g->id}}" @selected($selected_group == $g->id)>{{$g->GRUP_NAME}}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <x-datatable id="myTable" :title="$title" :subtitle="$subTitle" :cols="$cols" :items="$items"
                :atts="$atts" />
        </div>
    </div>
@endsection
