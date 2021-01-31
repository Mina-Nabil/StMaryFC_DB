@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <x-datatable id="myTable" :title="$title" :subtitle="$subTitle" :cols="$cols" :items="$items" :atts="$atts" />
    </div>
</div>

<script>
    function deleteAttendace(id) {
        Swal.fire({
        text: "Are you sure you want to delete the attendace?",
        icon: "warning",
        showCancelButton: true,
        }).then((isConfirm) => {
            if(isConfirm.value){


    
                var http = new XMLHttpRequest();
                var url = "{{$deleteAttendanceURL}}" + id ;  
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
        })
    }

</script>
@endsection