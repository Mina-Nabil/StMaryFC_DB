@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <x-datatable  id="myTable" :title="$title" :subtitle="$subTitle" :cols="$cols" :items="$items" :atts="$atts" />
    </div>
</div>

<script> 

    function deleteAttendace(id) {
        var http = new XMLHttpRequest();
        var url = "{{$deleteAttendanceURL}}" ;  
        http.open('POST', url, true);

        http.onreadystatechange = function(ret) {
            if (this.readyState == 4 && this.status == 200) {
                try {
                    location.reload();
                } catch(e) {
                  
                }
            }
        };
        http.send();
    }

</script>
@endsection