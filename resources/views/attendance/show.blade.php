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
                var url = "{{$deleteAttendanceURL}}" + "/" + id ;  
                http.open('GET', url);

                http.onreadystatechange = function(ret) {
                    if (this.readyState == 4 && this.status == 200 && this.responseText == "1") {
                        try {
                            Swal.fire({
                                text: "Attendance deleted",
                                icon: "success",
                            })
                            var rowAho = document.getElementById('row' + id);
                            rowAho.style.display = "none"
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