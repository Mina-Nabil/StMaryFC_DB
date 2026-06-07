@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ $formTitle }}</h4>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form class="form pt-3" method="post" action="{{ url($formURL) }}">
                    @csrf
                    <input type=hidden name=id value="{{ isset($template) ? $template->id : '' }}">

                    <div class="form-group">
                        <label>Template Name*</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Template Name" name=name
                                value="{{ isset($template) ? $template->name : old('name') }}" required>
                        </div>
                        <small class="text-danger">{{ $errors->first('name') }}</small>
                    </div>

                    <div class="form-group">
                        <label>Message Body*</label>
                        <textarea id="body" class="form-control" name="body" rows="10"
                            placeholder="Type the message, click a key below to insert a placeholder" required>{{ isset($template) ? $template->body : old('body') }}</textarea>
                        <small class="text-danger">{{ $errors->first('body') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="mb-1">Available keys <small class="text-muted">(click to insert at cursor)</small></label>
                        <div>
                            @foreach ($keys as $k)
                                <button type="button" class="btn btn-outline-info btn-sm mb-1 key-btn"
                                    data-key="{{ $k['key'] }}"
                                    title="{{ $k['desc'] }}{{ $k['app'] ? '' : ' (system scenarios only)' }}">
                                    {{ $k['key'] }}
                                </button>
                            @endforeach
                        </div>
                        <ul class="mt-2 mb-0" style="font-size:12px; padding-left:18px;">
                            @foreach ($keys as $k)
                                <li>
                                    <code>{{ $k['key'] }}</code> — {{ $k['desc'] }}
                                    @unless ($k['app'])
                                        <span class="label label-warning">system only</span>
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1"
                                {{ !isset($template) || $template->is_active ? 'checked' : '' }}>
                            Active
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success mr-2">Submit</button>
                    @if (isset($template))
                        <a href="{{ url($homeURL) }}" class="btn btn-dark">Cancel</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <x-datatable id="myTable" :title="$title" :subtitle="$subTitle" :cols="$cols" :items="$items" :atts="$atts" />
    </div>
</div>

<script>
    (function () {
        var body = document.getElementById('body');
        document.querySelectorAll('.key-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-key');
                var start = body.selectionStart;
                var end = body.selectionEnd;
                var text = body.value;
                body.value = text.slice(0, start) + key + text.slice(end);
                var caret = start + key.length;
                body.focus();
                body.setSelectionRange(caret, caret);
            });
        });
    })();
</script>
@endsection
