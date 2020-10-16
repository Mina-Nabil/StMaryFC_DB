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

</script>

<div class="card">
    <div class="card-body">
        @if($cardTitle)
        <h4 class="card-title">{{$title}}</h4>
        <h6 class="card-subtitle">{{$subtitle}}</h6>
        @endif
        <div class="table-responsive m-t-5">
            <table id="{{$id}}" class="table color-bordered-table table-striped full-color-table full-info-table hover-table" data-display-length='-1' data-order="[]">
                <thead>
                    <tr>
                        @foreach($cols as $col)
                        <th>{{$col}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>

                    @foreach($items as $item)
                    <tr>
                        @foreach($atts as $att)

                        @if(is_array($att))
                        @if(array_key_exists('edit', $att))
                        <td><a href="{{ url( $att['edit']['url'] . $item->{$att['edit']['att']}) }}"><img src="{{ asset('images/edit.png') }}" width=25 height=25></a></td>
                        @elseif(array_key_exists('foreign', $att))
                        <td>{{ $item->{$att['foreign'][0]}->{$att['foreign'][1]}  }}</td>
                        @elseif(array_key_exists('sumForeign', $att))
                        <td>{{ $item->{$att['sumForeign']['rel']}->sum($att['sumForeign']['att'])  }}</td>
                        @elseif(array_key_exists('url', $att))
                        <td><a href="{{ url($att['url'][0]) }}">{{ $item->{$att['url']['att']}  }}</a></td>
                        @elseif(array_key_exists('remoteURL', $att))
                        <td><a target="_blank" href="{{ url($item->{$att['remoteURL']['att']}) }}">
                                {{ (strlen($item->{$att['remoteURL']['att']}) < 15 ) ? $item->{$att['remoteURL']['att']} : substr($item->{$att['remoteURL']['att']},0,26).'..'  }}</a></td>
                        @elseif(array_key_exists('verified', $att))
                        <td>{{ $item->{$att['verified']['att']}  }}
                            @if($item->{$att['verified']['isVerified']})
                            <i class="fas fa-check-circle" style="color:lightgreen">
                                @endif
                        </td>
                        @elseif(array_key_exists('dynamicUrl', $att))
                        <td><a href="{{ url($att['dynamicUrl'][0].$item->{$att['dynamicUrl']['val']}) }}">{{ $item->{$att['dynamicUrl']['att']}  }}</a></td>
                        @elseif(array_key_exists('state', $att))
                        <td><span class="label {{ $att['state']['classes'][$item->{$att['state']['att']}] }}">{{ $item->{$att['state']['rel']}->{$att['state']['foreignAtt']}  }}</span></td>
                        @elseif(array_key_exists('stateQuery', $att))
                        <td>
                            @if(array_key_exists('url', $att['stateQuery']))
                            <a href="{{ url($att['stateQuery']['url'] . $item->{$att['stateQuery']['urlAtt']})}}">
                                @endif
                                <button class="label {{ $att['stateQuery']['classes'][$item->{$att['stateQuery']['att']}] }}">{{ $item->{$att['stateQuery']['foreignAtt']}  }}</button>
                                @if(array_key_exists('url', $att['stateQuery']))
                            </a>
                            @endif
                        </td>
                        @elseif(array_key_exists('toggle', $att))
                        <td><a href="javascript:void(0);">
                                <button class="label {{ $att['toggle']['classes'][$item->{$att['toggle']['att']}] }}"
                                    onclick="confirmAndGoTo('{{url($att['toggle']['url'] . $item->id)}}', '{{ $att['toggle']['actions'][$item->{$att['toggle']['att']}] }}')">
                                    {{ $att['toggle']['states'][$item->{$att['toggle']['att']}]  }}</button>
                            </a>
                        </td>
                        @elseif(array_key_exists('date', $att))
                        <td>{{ $item->{$att['date']['att']}->format($att['date']['format'])  }}</a></td>
                        @elseif(array_key_exists('attUrl', $att))
                        <td><a href="{{ url($att['attUrl']['url'] . '/' . $item->{$att['attUrl']['urlAtt']}) }}">{{ $item->{$att['attUrl']['shownAtt']}  }}</a></td>
                        @elseif(array_key_exists('urlOrStatic', $att))
                        @isset($item->{$att['urlOrStatic']['shownAtt']})
                        <td><a href="{{ url($att['urlOrStatic']['url'] . '/' . $item->{$att['urlOrStatic']['urlAtt']}) }}">{{ $item->{$att['urlOrStatic']['shownAtt']}  }}</a></td>
                        @else 
                        <td>{{ $item->{$att['urlOrStatic']['static']}  }}</td>
                        @endisset
                        @elseif(array_key_exists('foreignUrl', $att))
                        <td><a href="{{ url($att['foreignUrl'][0] . '/' . $item->{$att['foreignUrl']['1']}) }}">{{ $item->{$att['foreignUrl'][2]}->{$att['foreignUrl'][3]}  }}</a></td>
                        @elseif(array_key_exists('attOrAtt', $att))
                        <td>{{  isset($item->{$att['attOrAtt']['basicAtt']}) ? $item->{$att['attOrAtt']['basicAtt']} :  $item->{$att['attOrAtt']['otherAtt']} ?? ''  }}</td>
                        @elseif(array_key_exists('assetImg', $att))
                        <td>   
                            <img src="{{ asset( 'storage/'. $item->{$att['assetImg']['filename']}) }}" height="36" style="width: auto" />
                        </td>
                        @endif
                        @else
                        <td>{{ $item->{$att} }}</td>
                        @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="{{ asset('assets/node_modules/jquery/jquery-3.2.1.min.js') }}"></script>
<script src="{{ asset('assets/node_modules/datatables/datatables.min.js') }}"></script>
<script>
    $(function () {
            $(function () {

                var table = $('#{{$id}}').DataTable({
                    "displayLength": 25,
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            title: 'Whale Dashboard',
                            footer: true,
                        }
                    ]
                });
            })
        })
</script>