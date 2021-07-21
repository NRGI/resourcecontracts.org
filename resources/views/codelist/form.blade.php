@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
@stop

<div class="form-group">
    <label class="col-md-4 control-label">{{ trans('codelist.english') }} <span class="red">*</span></label>

    <div class="col-md-6">
        {!! Form::text('en', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">{{ trans('codelist.french') }} <span class="red">*</span></label>

    <div class="col-md-6">
        {!! Form::text('fr', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">{{ trans('codelist.arabic') }} </label>

    <div class="col-md-6">
        {!! Form::text('ar', null, ['class' => 'form-control']) !!}
    </div>
</div>

{{ Form::hidden('type', $type) }}


<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        <button type="submit" class="btn btn-primary">
            {{ trans('codelist.submit') }}
        </button>
        <a href={{ route('codelist.list', $type) }} class="btn btn-danger form-cancel-btn">
            {{ trans('Cancel') }}
        </a>
    </div>
</div>

@section('script')
@endsection
