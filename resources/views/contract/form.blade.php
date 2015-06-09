@if($action == 'add')
    <div class="browse-file">
        <input type="file" name="file" class="jfilestyle" id="jfilestyle-0" style="position: fixed; left: -500px;">

        <div class="jquery-filestyle " style="display: inline;"><input type="text" style="width:200px" disabled="">
            <label for="jfilestyle-0"><i class="icon-folder-open"></i> <span>Choose file</span></label></div>
    </div>
@endif

<div class="input-list-wrapper">
    <div class="input-wrapper">
        {!! Form::label('project_title')!!}
        {!! Form::text('project_title',
        isset($contract->metadata->project_title)?$contract->metadata->project_title:null,
        ["class"=>"form-control"])!!}
    </div>

    <div class="input-wrapper input-left-wrapper">
        {!! Form::label('country')!!}
        {!! Form::select('country', config('nrgi.country'),
        isset($contract->metadata->country)?$contract->metadata->country:null, ["class"=>"form-control"])!!}
    </div>
    <div class="input-wrapper input-right-wrapper">
        {!! Form::label('language')!!}
        {!! Form::select('language', config('nrgi.language'),
        isset($contract->metadata->language)?$contract->metadata->language:null, ["class"=>"form-control"])!!}
    </div>

    <div class="input-wrapper input-left-wrapper">
        {!! Form::label('signature_year')!!}
        {!! Form::text('signature_year',
        isset($contract->metadata->signature_year)?$contract->metadata->signature_year:null,
        ["class"=>"form-control"])!!}
    </div>

    <div class="input-wrapper input-right-wrapper">
        {!! Form::label('signature_date')!!}
        {!! Form::text('signature_date',
        isset($contract->metadata->signature_date)?$contract->metadata->signature_date:null,
        ["class"=>"form-control"])!!}
    </div>


    <div class="input-wrapper input-left-wrapper">
        {!! Form::label('resource')!!}
        {!! Form::select('resource', config('nrgi.resource'),
        isset($contract->metadata->resource)?$contract->metadata->resource:null, ["class"=>"form-control"])!!}
    </div>

    <div class="input-wrapper input-right-wrapper">
        <label for="">Contract Term</label>
        {!! Form::text('contract_term',
        isset($contract->metadata->contract_term)?$contract->metadata->contract_term:null,
        ["class"=>"form-control"])!!}
    </div>
    {!! Form::submit('Submit',['class'=>'btn save-btn']) !!}

</div>