@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <style>
        .other_toc, .dt {
            margin-top: 10px;
        }
    </style>
@stop

<?php
$corporate_groups = config('groups');
$groups = array();
foreach ($corporate_groups as $group) {
    $groups[$group['name']] = $group['name'];
}
asort($groups);
$groups = [0=>'Select'] + $groups;
?>

@if($action == 'add')
    <div class="form-group">
        <label for="Select PDF" class="col-sm-2 control-label">@lang('contract.contract_file') <span
                    class="red">*</span></label>

        <div class="col-sm-7">
            {!! Form::file('file', ['class'=>'required'])!!}
            <p class="help-block">@lang('contract.pdf_only').</p>
        </div>
    </div>
@endif

<div class="form-group">
    <label for="contract_name" class="col-sm-2 control-label">@lang('contract.contract_name') <span class="red">*</span></label>

    <div class="col-sm-7">
        {!! Form::text('contract_name',
        isset($contract->metadata->contract_name)?$contract->metadata->contract_name:null,
        ["class"=>"required form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('contract_identifier', trans('contract.contract_identifier'), ['class'=>'col-sm-2
    control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('contract_identifier',
        isset($contract->metadata->contract_identifier)?$contract->metadata->contract_identifier:null,
        ["class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('language', trans('contract.language'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::select('language',
        [''=>trans('codelist/language')['major'],'Other'=>trans('codelist/language')['minor']],
        isset($contract->metadata->language)?$contract->metadata->language:null, ["class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    <label for="country" class="col-sm-2 control-label">@lang('contract.country') <span class="red">*</span></label>

    <div class="col-sm-7">
        <?php $country_list = ['' => 'select'] + $country;?>
        {!! Form::select('country', $country_list ,
        isset($contract->metadata->country->code)?$contract->metadata->country->code:null, ["class"=>"required
        form-control"])!!}
        <label id="country-error" class="error" for="country"></label>
    </div>
</div>

<div class="form-group">
    {!! Form::label('resource', trans('contract.resource'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::select('resource[]', trans('codelist/resource'),
        isset($contract->metadata->resource)?$contract->metadata->resource:null, ['multiple'=>'multiple',
        "class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('government_entity', trans('contract.government_entity'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('government_entity',
        isset($contract->metadata->government_entity)?$contract->metadata->government_entity:null,
        ["class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('government_identifier', trans('contract.government_identifier'), ['class'=>'col-sm-2
    control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('government_identifier',
        isset($contract->metadata->government_identifier)?$contract->metadata->government_identifier:null,
        ["class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('type_of_contract', trans('contract.type_of_contract'), ['class'=>'col-sm-2
    control-label'])!!}


    <div class="col-sm-7">
        <?php
        $toc = isset($contract->metadata->type_of_contract) ? $contract->metadata->type_of_contract : old('type_of_contract');
        if (!in_array($toc, trans('codelist/contract_type')) AND $toc != '') {
            $toc = 'Other';
        }
        ?>
        {!! Form::select('type_of_contract', ['' => 'select']+trans('codelist/contract_type'),
        $toc,
        ["class"=>"form-control"])!!}

        @if($toc == 'Other')
            {!! Form::text('type_of_contract',
            isset($contract->metadata->type_of_contract)?$contract->metadata->type_of_contract:null,
            ["id" =>'', "class"=>"form-control other_toc"])!!}
        @endif

    </div>
</div>

<div class="form-group">
    <label for="signature_date" class="col-sm-2 control-label">@lang('contract.signature_date')</label>

    <div class="col-sm-7">
        {!! Form::text('signature_date',
        isset($contract->metadata->signature_date)?$contract->metadata->signature_date:null,
        ["class"=>"datepicker form-control", 'placeholder' => 'YYYY-MM-DD'])!!}
    </div>
</div>

<div class="form-group">
    <?php
    $dt = isset($contract->metadata->document_type) ? $contract->metadata->document_type : old('document_type');
    if (!in_array($dt, trans('codelist/documentType')) AND $dt != '') {
        $dt = 'Others';
    }
    ?>
    {!! Form::label('document_type', trans('contract.document_type'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::select('document_type', trans('codelist/documentType'),
        $dt, ["class"=>"form-control"])!!}
        @if($dt == 'Others')
            {!! Form::text('document_type',
            isset($contract->metadata->document_type)?$contract->metadata->document_type:null,
            ["id" =>'', "class"=>"form-control dt"])!!}
        @endif
    </div>
</div>

<h3>@lang('contract.company')</h3>
<hr/>
<div class="company">
    @if(isset($contract->metadata->company) || old('company'))
        <?php
        $companies = empty(old('company')) ? $contract->metadata->company : old('company');
        $i = 0;
        ?>
        @if(count($companies)>0)
            @foreach($companies as $k => $v)
                <div class="item" {{$k ==0 ? 'id=template' : ''}}>
                    <div class="form-group">
                        {!! Form::label('company_name', trans('contract.company_name'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][name]",
                            isset($v->name)?$v->name:null,
                            ["class"=>"form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('participation_share', trans('contract.participation_share'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::input('text',"company[$i][participation_share]",isset($v->participation_share)?$v->participation_share:null
                            ,["class"=>"form-control","step"=>"any","min"=>0,"max"=>1])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('jurisdiction_of_incorporation',
                        trans('contract.jurisdiction_of_incorporation'),
                        ['class'=>'col-sm-2 control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::select("company[$i][jurisdiction_of_incorporation]", ['' => 'select'] + $country ,
                            isset($v->jurisdiction_of_incorporation)?$v->jurisdiction_of_incorporation:null,
                            ["class"=>"form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('registration_agency', trans('contract.registry_agency'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][registration_agency]",
                            isset($v->registration_agency)?$v->registration_agency:null,
                            ["class"=>"form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('incorporation_date', trans('contract.incorporation_date'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][company_founding_date]",
                            isset($v->company_founding_date)?$v->company_founding_date:null,
                            ["class"=>"datepicker form-control", 'placeholder' => 'YYYY-MM-DD'])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('company_address', trans('contract.company_address'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][company_address]",
                            isset($v->company_address)?$v->company_address:null,
                            ["class"=>"form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('company_number', trans('contract.company_number'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][company_number]",
                            isset($v->company_number)?$v->company_number:null,
                            ["class"=>"form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('parent_company', trans('contract.corporate_grouping'), ['class'=>'col-sm-2 control-label'])!!}
                        <div class="col-sm-7">
                            <?php if(isset($v->parent_company) && !in_array($v->parent_company,$groups)):
                                $groups[$v->parent_company] = $v->parent_company;
                             endif;?>

                            {!! Form::select("company[$i][parent_company]", $groups , isset($v->parent_company)?$v->parent_company:null , ['class' => 'form-control parent_company']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <a href="http://opencorporates.com" target="_blank"><i class="glyphicon glyphicon-link"></i> {!!
                            Form::label('open_corporate_id',trans('contract.open_corporate'), ['class'=>'col-sm-2
                            control-label'])!!}</a>

                        <div class="col-sm-7">
                            {!! Form::text("company[$i][open_corporate_id]",
                            isset($v->open_corporate_id)?$v->open_corporate_id:null,
                            ["class"=>"digit form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('operator',trans('contract.is_operator'),['class'=>'col-sm-2 control-label']) !!}
                        <div class="col-sm-7">

                             {!! Form::radio("company[$i][operator]", 1, ($v->operator==1)?true:false , ['class' => 'field']) !!} Yes
                             {!! Form::radio("company[$i][operator]", 0, ($v->operator==0)?true:false, ['class' => 'field']) !!} No
                             {!! Form::radio("company[$i][operator]", -1, ($v->operator==-1)?true:false, ['class' => 'field']) !!} Not Available


                        </div>
                    </div>
                    @if($k > 0)
                        <div class="delete">delete</div>
                    @endif

                </div>
                <?php $i ++;?>

            @endforeach
        @endif
    @else
        <div class="item">
            <div class="form-group">
                {!! Form::label('company_name', trans('contract.company_name'), ['class'=>'col-sm-2 control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][name]",null,["class"=>"form-control"])!!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('participation_share', trans('contract.participation_share'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::input('text',"company[0][participation_share]",null ,["class"=>"form-control","step"=>"any","min"=>0,"max"=>1])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('jurisdiction_of_incorporation', trans('contract.jurisdiction_of_incorporation'),
                ['class'=>'col-sm-2 control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::select('company[0][jurisdiction_of_incorporation]', ['' => 'select'] + $country ,
                    isset($contract->metadata->country->code)?$contract->metadata->country->code:null,
                    ["class"=>"form-control"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('registration_agency', trans('contract.registry_agency'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][registration_agency]",null,["class"=>"form-control"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('incorporation_date', trans('contract.incorporation_date'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][company_founding_date]",null,["class"=>"datepicker form-control",
                    'placeholder'
                    => 'YYYY-MM-DD'])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('company_address', trans('contract.company_address'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][company_address]",null,["class"=>"form-control"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('company_number', trans('contract.company_number'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][company_number]",null,["class"=>"form-control"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('parent_company', trans('contract.corporate_grouping'), ['class'=>'col-sm-2 control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::select('company[0][parent_company]', $groups , isset($v->parent_company)?$v->parent_company:null , ['class' => 'form-control parent_company']) !!}
                </div>
            </div>

            <div class="form-group">
                <a href="http://opencorporates.com" target="_blank"><i class="glyphicon glyphicon-link"></i> {!!
                    Form::label('open_corporate_id',trans('contract.open_corporate'), ['class'=>'col-sm-2
                    control-label'])!!}</a>

                <div class="col-sm-7">
                    {!! Form::text("company[0][open_corporate_id]",null,["class"=>"url form-control"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('operator',trans('contract.is_operator'),['class'=>'col-sm-2 control-label']) !!}
                <div class="col-sm-7">
                     {!! Form::radio('company[0][operator]', 1, false, ['class' => 'field']) !!} Yes
                     {!! Form::radio('company[0][operator]', 0, false, ['class' => 'field']) !!} No
                     {!! Form::radio('company[0][operator]', -1, true, ['class' => 'field']) !!} Not Available
                </div>
            </div>
        </div>

    @endif

</div>


<div type="button" class="btn btn-default new-company">Add new company</div>

<h3>@lang('contract.license_and_project')</h3>
<hr/>
<div class="form-group">
    {!! Form::label('project_title', trans('contract.project_title'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('project_title',
        isset($contract->metadata->project_title)?$contract->metadata->project_title:null,
        ["class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('project_identifier', trans('contract.project_identifier'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('project_identifier',
        isset($contract->metadata->project_identifier)?$contract->metadata->project_identifier:null,
        ["class"=>"form-control"])!!}
    </div>
</div>

<div class="concession">
    @if(isset($contract->metadata->concession) || old('concession'))
        <?php
        $concession = empty(old('concession')) ? $contract->metadata->concession : old('concession');
        $j = 0;
        ?>

        @if(count($concession)>0)
            @foreach($concession as $k => $v)
                <div class="con-item" {{$k ==0 ? 'id=template' : ''}}>
                    <div class="form-group">
                        {!! Form::label('license_name', trans('contract.license_name'), ['class'=>'col-sm-2 control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("concession[$j][license_name]",
                            isset($v->license_name)?$v->license_name:null,
                            ["class"=>"form-control"])!!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('license_identifier', trans('contract.license_identifier'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("concession[$j][license_identifier]",
                            isset($v->license_identifier)?$v->license_identifier:null,
                            ["class"=>"form-control"])!!}
                        </div>

                    </div>
                    @if($k>0)
                        <div class="delete">delete</div>
                    @endif
                </div>

                <?php $j ++?>
            @endforeach
        @endif
    @else
        <div class="con-item">
            <div class="form-group">
                {!! Form::label('license_name', trans('contract.license_name'), ['class'=>'col-sm-2 control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("concession[0][license_name]",null,
                    ["class"=>"form-control"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('license_identifier', trans('contract.license_identifier'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("concession[0][license_identifier]",null,
                    ["class"=>"form-control"])!!}

                </div>

            </div>
        </div>
    @endif

</div>

<div class="btn btn-default new-concession">Add new License</div>

<h3>@lang('contract.source')</h3>
<hr/>

<div class="form-group">
    {!! Form::label('source_url', trans('contract.source_url'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('source_url',
        isset($contract->metadata->source_url)?$contract->metadata->source_url:null,
        ["class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('disclosure_mode', trans('contract.disclosure_mode'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::select('disclosure_mode', ['' => 'select']+trans('codelist/disclosure_mode'),
        isset($contract->metadata->disclosure_mode)?$contract->metadata->disclosure_mode:null, [
        "class"=>"form-control"])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('date_retrieval', trans('contract.date_of_retrieval'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('date_retrieval',
        isset($contract->metadata->date_retrieval)?$contract->metadata->date_retrieval:null,
        ["class"=>"datepicker form-control", 'placeholder' => 'YYYY-MM-DD'])!!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('category', trans('contract.category'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        @foreach(config('metadata.category') as $key => $category)
            <label class="checkbox-inline">
                {!! Form::checkbox('category[]', $key,
                (isset($contract->metadata->category) && is_array($contract->metadata->category) && in_array($key,
                $contract->metadata->category))? true : null)!!}
                {{$category}}
            </label>
        @endforeach
    </div>
</div>

<h3>@lang('contract.associated_contracts')</h3>
<hr>
<div class="form-group">
    {!! Form::label('translated_from', trans('contract.parent_document'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::select('translated_from',['' => 'select']+$contracts, isset($contract->metadata->translated_from)?$contract->metadata->translated_from:null, ["class"=>"form-control"])!!}

    </div>
</div>
<div class="form-group support-form-group">
    {!! Form::label('translated_from', trans('contract.supporting_documents'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::select('',['' => 'select']+$contracts,null, ["class"=>"form-control select-document"])!!}
    </div>
</div>
<?php $docId = []; ?>
<div id="selected-document" class="selected-document">
    @if(!empty($supportingDocument))
        @foreach($supportingDocument as $doc)
            <div class="document">
                <a href="{{route('contract.show',$doc['id'])}}">{{$doc['contract_name']}}</a><br>
                <input type="hidden" name="supporting_document[]" value="{{$doc['id']}}">
                <?php
                array_push($docId, $doc['id']);
                ?>
                <div class="delete" id="{{$doc['id']}}">delete</div>
            </div>
        @endforeach
    @endif
</div>
@section('script')
    <script src="{{asset('js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/jquery.datetimepicker.js')}}"></script>
    <script src="{{asset('js/mustache.min.js')}}"></script>
    <script src="{{asset('js/lib/underscore.js')}}"></script>
    <script src="{{asset('js/lib/backbone.js')}}"></script>

    @include('contract.company_template')
    <script>
        var input = '<input class="form-control other_toc" name="type_of_contract" type="text">';

        $(document).on('change', '#type_of_contract', function () {
            if (($(this).val() == 'Other')) {
                $(this).parent().append(input)
            } else {
                if ($('.other_toc').length) {
                    input = $('.other_toc');
                }
                $('.other_toc').remove();
            }
        });

        var input_dt = '<input class="form-control dt" name="document_type" type="text">';

        $(document).on('change', '#document_type', function () {
            if (($(this).val() == 'Others')) {
                $(this).parent().append(input_dt)
            } else {
                if ($('.dt').length) {
                    input_dt = $('.dt');
                }
                $('.dt').remove();
            }
        });


        var i = {{$i or 0}};
        var j = {{$j or 0}};
        var country_list = {!!json_encode($country_list)!!};
        var contracts = {!!json_encode($contracts)!!};
        var docId = {{json_encode($docId)}};

    </script>
    <script src="{{asset('js/contract.js')}}"></script>
@stop


<div class="form-action">
    <div class="col-sm-7 col-lg-offset-2">
        {!! Form::submit(trans('contract.submit'),['class'=>'btn btn-lg pull-right btn-primary']) !!}
    </div>
</div>
