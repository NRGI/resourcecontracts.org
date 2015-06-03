<div class="form-group">
    {!! Form::label('project_title')!!} <span class="red">*</span>
    {!! Form::text('project_title', isset($contract->metadata->project_title)?$contract->metadata->project_title:null, ["class"=>"form-control"])!!}
</div>

@if($action == 'add')
    <div class="form-group">
        {!! Form::label('file')!!} <span class="red">*</span>
        {!! Form::file('file')!!}
    </div>
@endif

<div class="form-group">
    {!! Form::label('country')!!}
    {!! Form::text('country', isset($contract->metadata->country)?$contract->metadata->country:null, ["class"=>"form-control"])!!}
</div>

<div class="form-group">
    {!! Form::label('language')!!}
    {!! Form::text('language', isset($contract->metadata->language)?$contract->metadata->language:null, ["class"=>"form-control"])!!}
</div>

<div class="form-group">
    {!! Form::label('signature_date')!!}
    {!! Form::text('signature_date', isset($contract->metadata->signature_date)?$contract->metadata->signature_date:null, ["class"=>"form-control"])!!}
</div>

<div class="form-group">
    {!! Form::label('signature_year')!!}
    {!! Form::text('signature_year', isset($contract->metadata->signature_year)?$contract->metadata->signature_year:null, ["class"=>"form-control"])!!}
</div>

<div class="form-group">
    {!! Form::label('type_of_mining')!!}
    {!! Form::text('type_of_mining', isset($contract->metadata->type_of_mining)?$contract->metadata->type_of_mining:null, ["class"=>"form-control"])!!}
</div>

<div class="form-group">
    {!! Form::label('resource')!!}
    {!! Form::text('resource', isset($contract->metadata->resource)?$contract->metadata->resource:null, ["class"=>"form-control"])!!}
</div>

<div class="form-group">
    {!! Form::label('contract_term')!!}
    {!! Form::text('contract_term', isset($contract->metadata->contract_term)?$contract->metadata->contract_term:null, ["class"=>"form-control"])!!}
</div>

<div class="form-action">
    {!! Form::submit('Submit',['class'=>'btn btn-primary']) !!}
</div>