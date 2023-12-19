@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('css/jquery-ui.css')}}" rel="stylesheet"/>
    <style>
        .other_toc, .dt, .disclosure_mode_other, .corporate_grouping_other {
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
$groups = ['' => trans('Select')] + $groups + ['Other' => 'Other'];

$govt_entity = [];
$govEntityOnly = [];
$govEntity = config('governmentEntities');
$country_code = old('country', isset($contract->metadata->country->code) ? $contract->metadata->country->code : '');
$govEntityOnly = isset($govEntity->$country_code) ? $govEntity->$country_code : [];

if (!empty($govEntityOnly)) {
    foreach ($govEntityOnly as $entity) {
        $govt_entity[$entity->entity] = $entity->entity;
    }
}

if (!empty($contract->metadata->government_entity)) {
    foreach ($contract->metadata->government_entity as $gov) {
        if (isset($gov->entity) && !in_array($gov->entity, $govt_entity)) {
            $govt_entity[$gov->entity] = $gov->entity;
        }
    }
}
asort($documentTypeList);
?>

@if($action == 'add')
    <div class="form-group">
        <label for="Select PDF" class="col-sm-2 control-label">@lang('contract.contract_file') <span
                    class="red">*</span></label>

        <div class="col-sm-7">
            {!! Form::file('file', ['class'=>'required' , 'id' => 'file'])!!}
        </div>
    </div>
@endif

@if($action == 'edit' && !isset($edit_trans))
    <div id="new-document" style="display: none" class="form-group">
        <label for="Select PDF" class="col-sm-2 control-label">@lang('contract.contract_file')</label>

        <div class="col-sm-7">
            {!! Form::file('file', [ 'id' => 'file'])!!}
        </div>
    </div>
    <div class="form-group">
        <label for="Select PDF" class="col-sm-2 control-label"></label>

        <div class="col-sm-7">
            <a target="_blank" href="{{$contract->file_url}}">@lang('contract.view_document')</a> | <a id="show-new-document"
                                                                                     href="javascript:void();">@lang('contract.change')</a>
        </div>
    </div>

@endif

<div class="form-group">
    <label for="contract_name" class="col-sm-2 control-label">@lang('contract.contract_name') <span class="red">*</span></label>
    <?php
    if ($action == 'edit') {
        $contract_name = isset($contract->metadata->contract_name) ? $contract->metadata->contract_name : null;
    } else $contract_name = null;
    ?>
    <div class="col-sm-7">
            {!! Form::text('contract_name', $contract_name, ["class"=>"required form-control contract_name"])!!}
        <span id="generate-contract-name" class="btn btn-default"> @lang('contract.generate_name')
        </span>
        <span class="label loading" style="display:none"><img src="{{asset('/images/loading.gif')}}" style="width:25px" /></span>
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'contract_name','metadata') !!}
    @endif
</div>

<div class="form-group">
    {!! Form::label('contract_identifier', trans('contract.contract_identifier'), ['class'=>'col-sm-2
    control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('contract_identifier',
        isset($contract->metadata->contract_identifier)?$contract->metadata->contract_identifier:null,
        ["class"=>"form-control"])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'contract_identifier','metadata') !!}
    @endif
</div>

<div class="form-group">
    <label for="language" class="col-sm-2 control-label">@lang('contract.language') <span class="red">*</span></label>

    <div class="col-sm-7">
        {!! Form::select('language',
        [''=>trans('codelist/language',[],null,$locale)['major'],
        'Other'=>trans('codelist/language',[],null,$locale)['minor']],
        isset($contract->metadata->language)?$contract->metadata->language:null, ["class"=>"required form-control"])!!}
        <label id="language-error" class="error" for="language"></label>
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'language','metadata') !!}
    @endif
</div>

<div class="form-group">
    <label for="country" class="col-sm-2 control-label">@lang('contract.country') <span class="red">*</span></label>

    <div class="col-sm-7">
        <?php
            foreach($country as $k => $cn)
                {
					$country[$k] = trans('codelist/country.'.$k, [],null,$locale);
                }

        $country_list = ['' => trans('global.select')] + $country;?>
        {!! Form::select('country', $country_list ,
        isset($contract->metadata->country->code)?$contract->metadata->country->code:null, ["class"=>"required
        form-control" , "id" => "country"])!!}
        <label id="country-error" class="error" for="country"></label>
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'country','metadata') !!}
    @endif
</div>
<?php
$resources = isset($contract->metadata->resource)?$contract->metadata->resource :[];

foreach ($resources as $item) {
	if(!isset($resourceList[$item])){
		$resourceList[$item] = $item;
	}
}
?>
<div class="form-group">
    <label for="resource" class="col-sm-2 control-label">@lang('contract.resource') <span class="red">*</span></label>

    <div class="col-sm-7">
        {!! Form::select('resource[]', $resourceList, $resources, ['multiple'=>'multiple', "class"=>"required form-control resource-list"])!!}
        <label id="resource[]-error" class="error" for="resource[]"></label>
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'resource','metadata') !!}
    @endif
</div>


<div class="government_entity">
    @if(isset($contract->metadata->government_entity) || old('government_entity'))
        <?php
        $governmentEntity = empty(old('government_entity')) ? $contract->metadata->government_entity : old('government_entity');
        $g = 0;
        ?>

        @if(count($governmentEntity)>0)
            @foreach($governmentEntity as $k => $v)
                <div class="government-item" {{$k ==0 ? 'id=template' : ''}}>
                    <div class="form-group">
                        <label for="entity" class="col-sm-2 control-label">@lang('contract.government_entity')</label>

                        <div class="col-sm-7">
                            {!! Form::select("government_entity[$g][entity]", [''=>trans('Select')] + $govt_entity,
                            isset($v->entity)?$v->entity:null,
                            ["class"=>"form-control el_government_entity"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'entity-'.$k,'metadata') !!}
                        @endif

                    </div>

                    <div class="form-group">
                        {!! Form::label('identifier', trans('contract.government_identifier'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("government_entity[$g][identifier]",
                            isset($v->identifier)?$v->identifier:null,
                            ["class"=>"form-control el_government_identifier" ,"readonly" => "true"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'identifier-'.$k,'metadata') !!}
                        @endif
                    </div>
                    @if($k>0)
                        <div data-key="{{$k}}" class="delete">delete</div>
                    @endif
                </div>

                <?php $g ++?>
            @endforeach
        @endif
    @else
        <div class="government-item">
            <div class="form-group">
                <label for="entity" class="col-sm-2 control-label">@lang('contract.government_entity') <span class="red">*</span></label>

                <div class="col-sm-7">
                    {!! Form::select("government_entity[0][entity]",[''=>trans('Select')] + $govt_entity,null,
                    ["class"=>"form-control el_government_entity" , "id" => "government_0_entity"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('government_identifier', trans('contract.government_identifier'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("government_entity[0][identifier]",null,
                    ["class"=>"form-control el_government_identifier" , "id" => "government_0_identifier", "readonly" => "true"])!!}

                </div>
            </div>
        </div>
    @endif

</div>

<button type="button" class="btn btn-default new-government-entity add-new-btn" id="addGov">@lang('contract.add_new_government_entity')</button>

<div class="form-group el_document_type">
    <?php
    if(isset($contract->file)){
        $dt = (isset($contract->metadata->document_type) && !isset($_GET['parent'])) ? $contract->metadata->document_type : old('document_type');
        if (!array_key_exists($dt, $documentTypeList) AND $dt != '') {
            $dt = 'Other';
        }
    }
    else {
        $dt = '';
    }
    ?>
    <label for="document_type" class="col-sm-2 control-label">@lang('contract.document_type') <span class="red">*</span></label>
    <div class="col-sm-7">
         {!! Form::select('document_type',[''=>trans('Select')]+ $documentTypeList, $dt, ["class"=>"required form-control", "id"=>"document_type"])!!}
        @if($dt == 'Other')
            {!! Form::text('document_type',
            isset($contract->metadata->document_type)?$contract->metadata->document_type:null,
            ["id" =>'', "class"=>"form-control dt"])!!}
        @endif
        <label id="document_type-error" class="error" for="document_type"></label>
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'document_type','metadata') !!}
    @endif
</div>

<div class="form-group">
    <label for="type_of_contract" class="col-sm-2 control-label">@lang('contract.type_of_contract')<span class="red">*</span></label>

    <div class="col-sm-7">
        <?php
            $toc = (isset($contract->metadata->type_of_contract) && !(isset($_GET['parent'])) )? $contract->metadata->type_of_contract : old('type_of_contract');
            $ct = $toc;
            $isTocOther = false;
            if (isset($ct) && is_array($ct) && count($ct) === 1 && count(array_diff($ct, $contractTypeList)) > 0) {
                $ct = array();
                $ct[0] = 'Other';
                $isTocOther = true;
            }
            ?>
            {!! Form::select('type_of_contract[]', $contractTypeList,
            $ct,
            ["multiple"=>"multiple", "class"=>" form-control", "id"=>"type_of_contract"])!!}
            @if($isTocOther)
                <span class="red input-required">*</span>
                <input class="form-control required other_toc" value="{{isset($toc[0])?$toc[0]:''}}"  name="type_of_contract[]" type="text">
            @endif

        <label id="type_of_contract-error" class="error" for="type_of_contract"></label>
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'type_of_contract','metadata') !!}
    @endif
</div>


<div class="form-group">
    <label for="signature_date" class="col-sm-2 control-label">@lang('contract.signature_date') </label>

    <div class="col-sm-7">
        {!! Form::text('signature_date',
        isset($contract->metadata->signature_date)?$contract->metadata->signature_date:null,
        ["class"=>"datepicker form-control signature_date", 'placeholder' => 'YYYY-MM-DD' , 'id' => 'signature_date'])!!}
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'signature_date','metadata') !!}
    @endif
</div>


<div class="form-group">
    <label for="signature_year" class="col-sm-2 control-label">@lang('contract.signature_year') <span class="red">*</span></label>
    <div class="col-sm-7">
        {!! Form::text('signature_year',
        isset($contract->metadata->signature_year)?$contract->metadata->signature_year:null,
        ["class"=>"required form-control signature_year",'placeholder' => 'YYYY' , 'id' => 'signature_year'])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'signature_year','metadata') !!}
    @endif
</div>

<div class="form-group">
    <label for="is_contract_signed" class="col-sm-2 control-label">@lang('contract.is_contract_signed') <span class="red"></span></label>
      <?php
        $checked = (isset($contract->metadata->is_contract_signed))?(integer)$contract->metadata->is_contract_signed:1;
        ?>
    <div class="col-sm-7">
        {!! Form::checkbox('is_contract_signed',1,$checked,["class"=>"is_contract_signed",'id' => 'is_contract_signed'])!!}
    </div>

    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'is_contract_signed','metadata') !!}
    @endif
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
                <?php
                $v = (object) $v;
                ?>
                <div class="item" {{$k ==0 ? 'id=template' : ''}}>
                    <div class="form-group">
                        <label for="company_name" class="col-sm-2 control-label">@lang('contract.company_name') <span class="red">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][name]",
                            isset($v->name)?$v->name:null,
                            ["class"=>"form-control company_name"] )!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'name-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('participation_share', trans('contract.participation_share'),
                        ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::input('text',"company[$i][participation_share]",isset($v->participation_share)?$v->participation_share:null
                            ,["class"=>"form-control participation_share","step"=>"any","min"=>0,"max"=>1])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'participation_share-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('jurisdiction_of_incorporation',
                        trans('contract.jurisdiction_of_incorporation'),
                        ['class'=>'col-sm-2 control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::select("company[$i][jurisdiction_of_incorporation]",
                            ['' => trans('global.select')] + trans('codelist/country',[],null,$locale) ,
                            isset($v->jurisdiction_of_incorporation)?$v->jurisdiction_of_incorporation:null,
                            ["class"=>"form-control jurisdiction_of_incorporation"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'jurisdiction_of_incorporation-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('registration_agency', trans('contract.registry_agency'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][registration_agency]",
                            isset($v->registration_agency)?$v->registration_agency:null,
                            ["class"=>"form-control registration_agency"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'registration_agency-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('incorporation_date', trans('contract.incorporation_date'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][company_founding_date]",
                            isset($v->company_founding_date)?$v->company_founding_date:null,
                            ["class"=>"datepicker form-control company_founding_date", 'placeholder' => 'YYYY-MM-DD'])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'company_founding_date-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('company_address', trans('contract.company_address'), ['class'=>'col-sm-2 control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][company_address]",
                            isset($v->company_address)?$v->company_address:null,
                            ["class"=>"form-control company_address"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'company_address-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('company_number', trans('contract.company_number'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("company[$i][company_number]",
                            isset($v->company_number)?$v->company_number:null,
                            ["class"=>"form-control company_number"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'company_number-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('parent_company', trans('contract.corporate_grouping'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            <?php
                            $parentCompany = isset($v->parent_company) ? $v->parent_company : '';
                            if (!empty($parentCompany) && !in_array($parentCompany, $groups)) {
                                $v->parent_company = 'Other';
                            }
                            ?>
                            <select name="<?php echo 'company[' . $i . '][parent_company]'; ?>" class="form-control corporate_grouping parent_company" id="corporate_grouping_0">
                                @foreach($groups as $key=>$value)
                                    <option value="{{$key}}" @if($key==$parentCompany) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>

                            @if(isset($v->parent_company) && $v->parent_company == 'Other')
                                {!! Form::text("company[$i][parent_company]",
                                $parentCompany,
                                ["id" =>'', "class"=>"form-control corporate_grouping_other"])!!}
                            @endif

                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'parent_company-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        <a href="http://opencorporates.com" target="_blank"><i class="glyphicon glyphicon-link"></i>
                            {!! Form::label('open_corporate_id',trans('contract.open_corporate'), ['class'=>'col-sm-2 control-label'])!!}
                        </a>

                        <div class="col-sm-7">
                            {!! Form::text("company[$i][open_corporate_id]",
                            isset($v->open_corporate_id)?$v->open_corporate_id:null,
                            ["class"=>"digit form-control open_corporate_id"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'open_corporate_id-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('operator',trans('contract.is_operator'),['class'=>'col-sm-2 control-label']) !!}
                        <div class="col-sm-7">
                            {!! Form::radio("company[$i][operator]", 1, (isset($v->operator) && $v->operator==1)?true:false , ['class' => 'field']) !!}
							{{trans('global.yes',[],null,$locale)}}
                            {!! Form::radio("company[$i][operator]", 0, (isset($v->operator) && $v->operator==0)?true:false, ['class' => 'field']) !!}
							{{trans('global.no',[],null,$locale)}}
                            {!! Form::radio("company[$i][operator]", -1, (isset($v->operator) && $v->operator==-1)?true:false, ['class' => 'field']) !!}
							{{trans('global.not_available',[],null,$locale)}}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'operator-'.$k,'metadata') !!}
                        @endif
                    </div>
                    @if($k > 0)
                        <div data-key="{{$k}}" class="delete">delete</div>
                    @endif
                </div>
                <?php $i ++;?>
            @endforeach
        @endif
    @else
        <div class="item">
            <div class="form-group">
                <label for="company_name" class="col-sm-2 control-label">@lang('contract.company_name') <span class="red">*</span></label>
                <div class="col-sm-7">
                    {!! Form::text("company[0][name]",null,["class"=>"form-control company_name" , "id"=> "company_0_name"])!!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('participation_share', trans('contract.participation_share'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::input('text',"company[0][participation_share]",null ,["class"=>"form-control","step"=>"any","min"=>0,"max"=>1 , "id"=> "company_0_participation_share"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('jurisdiction_of_incorporation', trans('contract.jurisdiction_of_incorporation'),
                ['class'=>'col-sm-2 control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::select('company[0][jurisdiction_of_incorporation]',
                    ['' => trans('global.select')] + trans('codelist/country',[],null,$locale) ,
                    isset($contract->metadata->country->code)?$contract->metadata->country->code:null,
                    ["class"=>"form-control" , "id"=> "company_0_jurisdiction"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('registration_agency', trans('contract.registry_agency'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][registration_agency]",null,["class"=>"form-control" , "id"=> "company_0_registration_agency"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('incorporation_date', trans('contract.incorporation_date'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][company_founding_date]",null,["class"=>"datepicker form-control",
                    'placeholder'
                    => 'YYYY-MM-DD' , "id" => "company_0_founding_date"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('company_address', trans('contract.company_address'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][company_address]",null,["class"=>"form-control" , "id" => "company_0_address"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('company_number', trans('contract.company_number'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("company[0][company_number]",null,["class"=>"form-control" , "id" => "company_0_number"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('parent_company', trans('contract.corporate_grouping'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::select('company[0][parent_company]', $groups , null , ['class' => 'form-control corporate_grouping parent_company','id'=>'corporate_grouping_0'])
                    !!}
                </div>
            </div>

            <div class="form-group">
                <a href="http://opencorporates.com" target="_blank"><i class="glyphicon glyphicon-link"></i> {!!
                    Form::label('open_corporate_id',trans('contract.open_corporate'), ['class'=>'col-sm-2
                    control-label'])!!}</a>

                <div class="col-sm-7">
                    {!! Form::text("company[0][open_corporate_id]",null,["class"=>"url form-control" , "id" => "company_0_open_corporate_id"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('operator',trans('contract.is_operator'),['class'=>'col-sm-2 control-label']) !!}
                <div class="col-sm-7">
                    {!! Form::radio('company[0][operator]', 1, false, ['class' => 'field' , 'id' => 'company_0_operator_yes']) !!}
					{{trans('global.yes',[],null,$locale)}}
                    {!! Form::radio('company[0][operator]', 0, false, ['class' => 'field' , 'id' => 'company_0_operator_no']) !!}
					{{trans('global.no',[],null,$locale)}}
                    {!! Form::radio('company[0][operator]', -1, true, ['class' => 'field']) !!}
					{{trans('global.not_available',[],null,$locale)}}
                </div>
            </div>
        </div>

    @endif

</div>


<div type="button" id="add_new_company" class="btn btn-default new-company add-new-btn">@lang('contract.add_new_company')</div>

<h3>@lang('contract.license_and_project')</h3>
<hr/>
<div class="form-group">
    {!! Form::label('project_title', trans('contract.project_name'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('project_title',
        isset($contract->metadata->project_title)?$contract->metadata->project_title:null,
        ["class"=>"form-control"])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'project_title','metadata') !!}
    @endif
</div>

<div class="form-group">
    {!! Form::label('project_identifier', trans('contract.project_identifier'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('project_identifier',
        isset($contract->metadata->project_identifier)?$contract->metadata->project_identifier:null,
        ["class"=>"form-control"])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'project_identifier','metadata') !!}
    @endif
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
                        {!! Form::label('license_name', trans('contract.license_name'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("concession[$j][license_name]",
                            isset($v->license_name)?$v->license_name:null,
                            ["class"=>"license-name form-control"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'license_name-'.$k,'metadata') !!}
                        @endif
                    </div>

                    <div class="form-group">
                        {!! Form::label('license_identifier', trans('contract.license_identifier'), ['class'=>'col-sm-2
                        control-label'])!!}
                        <div class="col-sm-7">
                            {!! Form::text("concession[$j][license_identifier]",
                            isset($v->license_identifier)?$v->license_identifier:null,
                            ["class"=>"license_identifier form-control"])!!}
                        </div>
                        @if($action == 'edit')
                            {!! discussion($discussions,$discussion_status, $contract->id,'license_identifier-'.$k,'metadata') !!}
                        @endif
                    </div>
                    @if($k>0)
                        <div data-key="{{$k}}" class="delete">{{trans('global.delete')}}</div>
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
                    ["class"=>"form-control license-name" , "id" => "license_name_0"])!!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('license_identifier', trans('contract.license_identifier'), ['class'=>'col-sm-2
                control-label'])!!}
                <div class="col-sm-7">
                    {!! Form::text("concession[0][license_identifier]",null,
                    ["class"=>"form-control license_identifier" , "id" => "license_identifier_0"])!!}
                </div>
            </div>
        </div>
    @endif

</div>

<div class="btn btn-default new-concession add-new-btn">@lang('contract.add_new_license')</div>

<h3>@lang('contract.source')</h3>
<hr/>

<div class="form-group">
    {!! Form::label('source_url', trans('contract.source_url'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('source_url',
        isset($contract->metadata->source_url)?$contract->metadata->source_url:null,
        ["class"=>"form-control"])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'source_url','metadata') !!}
    @endif
</div>

<div class="form-group">
    <?php
    $disclosure_mode = isset($contract->metadata->disclosure_mode) ? $contract->metadata->disclosure_mode : old(
            'disclosure_mode'
    );
    if (!in_array($disclosure_mode, trans('codelist/disclosure_mode')) AND $disclosure_mode != '') {
        $disclosure_mode = 'Other';
    }
    ?>
    <label for="disclosure_mode" class="col-sm-2 control-label">@lang('contract.disclosure_mode')</label>

    <div class="col-sm-7">
        <select class="form-control" name="disclosure_mode" id="disclosure_mode">
            <option value="">Select</option>
            @foreach(trans('codelist/disclosure_mode',[],null,$locale) as $key=>$value)
                <option value="{{$key}}" @if($key==$disclosure_mode) selected @endif>{{$value}}</option>
            @endforeach
        </select>

        @if($disclosure_mode == 'Other')
            {!! Form::text('disclosure_mode',
            isset($contract->metadata->disclosure_mode)?$contract->metadata->disclosure_mode:null,
            ["id" =>'disclosure_mode_other', "class"=>"form-control disclosure_mode_other"])!!}
        @endif
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'disclosure_mode','metadata') !!}
    @endif
</div>

<div class="form-group">
    {!! Form::label('disclosure_mode_text', trans('contract.disclosure_mode_text'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('disclosure_mode_text',
        isset($contract->metadata->disclosure_mode_text)?$contract->metadata->disclosure_mode_text:null,
        ["class"=>"form-control"])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'disclosure_mode_text','metadata') !!}
    @endif
</div>


<div class="form-group">
    {!! Form::label('date_retrieval', trans('contract.date_of_retrieval'), ['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        {!! Form::text('date_retrieval',
        isset($contract->metadata->date_retrieval)?$contract->metadata->date_retrieval:null,
        ["class"=>"datepicker form-control", 'placeholder' => 'YYYY-MM-DD'])!!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'date_retrieval','metadata') !!}
    @endif
</div>

<div class="form-group">
    <label for="category" class="col-sm-2 control-label">@lang('contract.category') <span class="red">*</span></label>

    <div class="col-sm-7">
        <?php
        $old_category = isset($contract->metadata->category) ? $contract->metadata->category : old('category');
        ?>
        @foreach(config('metadata.category') as $key => $category)

            <label class="checkbox-inline">
                <input name="category[]" {{(is_array($old_category) && in_array($key, $old_category)) ? "checked='checked'" : ''}} type="radio" value="{{$key}}" class="required"
                       id="category-{{$key}}"> {{$category}}
            </label>
        @endforeach
        <br>
        <label id="category[]-error" class="error" for="category[]"></label>
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'category','metadata') !!}
    @endif
</div>

<div class="landmatrix-page-wrap">
    <div class="form-group">
        {!! Form::label('deal_number' , trans('contract.deal_number') , ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-7">
            {!! Form::text('deal_number', isset($contract->metadata->deal_number)?$contract->metadata->deal_number:null, ['class' => 'form-control']) !!}
        </div>
        @if($action == 'edit')
            <div class="col-sm-3">
                {!! discussion($discussions,$discussion_status, $contract->id,'deal_number','metadata') !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('matrix_page' , trans('contract.matrix_page') , ['class' => 'col-sm-2 control-label']) !!}
        <div class="col-sm-7">
            {!! Form::text('matrix_page', isset($contract->metadata->matrix_page)?$contract->metadata->matrix_page:null, ['class' => 'form-control' , 'rows' => '2']) !!}
        </div>
        @if($action == 'edit')
            <div class="col-sm-3">
                {!! discussion($discussions,$discussion_status, $contract->id,'matrix_page','metadata') !!}
            </div>
        @endif
    </div>
</div>

<hr>
<div class="form-group">
    {!! Form::label('contract_note' , trans('contract.contract_note') , ['class' => 'col-sm-2 control-label']) !!}
    <div class="col-sm-7">
        {!! Form::textarea('contract_note', isset($contract->metadata->contract_note)?$contract->metadata->contract_note:null, ['class' => 'form-control' , 'rows' => '6' ]) !!}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'contract_note','metadata') !!}
    @endif
</div>

<h3>@lang('contract.associated_contracts')</h3>
<hr>
<div class="form-group">
    {!! Form::label('operator',trans('contract.is_supporting_document'),['class'=>'col-sm-2 control-label'])!!}
    <div class="col-sm-7">
        <?php
        $is_supporting_document_value = false;
        $is_parent_document_value = false;
        $disable_supporting = 'disabled';
        $disable_parent = 'disabled';
        if (isset($contract->metadata->is_supporting_document) && $contract->metadata->is_supporting_document == 1) {
            $is_supporting_document_value = true;
            $disable_supporting           = 'enabled';
        } else {
            $is_parent_document_value = true;
            $disable_parent           = 'enabled';
        }
        if ($action == 'add' && ($is_supporting)) {
            $is_supporting_document_value = true;
            $is_parent_document_value     = false;
            $disable_parent               = 'disabled';
            $disable_supporting           = 'enabled';
        }
        if ($action == 'edit' && isset($contract->metadata->is_supporting_document) && $contract->metadata->is_supporting_document == 0) {
            $is_supporting = false;
        }
        if ($action == 'add' && !$is_supporting) {
            $is_parent_document_value = true;
        }

        if ($action == 'edit' && empty($supportingDocument)) {
            $disable_supporting = 'enabled';
            $disable_parent     = 'enabled';
        }

        ?>
        <label class="checkbox-inline">
            {!! Form::radio("is_supporting_document", 1, $is_supporting_document_value , ['class' => 'field is-supporting-document',$disable_supporting]) !!}
			{{trans('global.yes',[],null,$locale)}}
        </label>
        <label class="checkbox-inline">
            {!! Form::radio("is_supporting_document", 0, $is_parent_document_value, ['class' => 'field is-supporting-document',$disable_parent]) !!}
			{{trans('global.no',[],null,$locale)}}
        </label>
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'is_supporting_document','metadata') !!}
    @endif

</div>
<div class="form-group parent-document"
     style="display: @if($action == 'edit' && isset($contract->metadata->is_supporting_document) &&  $contract->metadata->is_supporting_document==1 ?? ($is_supporting))block @else none @endif">
    {!! Form::label('translated_from', trans('contract.parent_document'), ['class'=>'col-sm-2 control-label parent-document-select'])!!}
    <?php
    $parent_contract = null;
    if ($action == 'edit' && $contract->getParentContract()) {
        $parent_contract = $contract->getParentContract();
    }
    if (isset($is_supporting) && $is_supporting) {
        $parent_contract = Request::get('parent');
    }
    ?>
    <div class="col-sm-7">
        {!! Form::select('translated_from',['' => trans('global.select')]+$contracts, $parent_contract,
        ["class"=>"form-control"])!!}
    </div>
</div>

<?php $docId = []; ?>
<div id="selected-document" class="selected-document">
    @if(!empty($supportingDocument))
        @foreach($supportingDocument as $doc)
            <div class="document">
                <a href="{{route('contract.edit',['contract' => $doc['id']])}}">{{$doc['contract_name']}}</a><br>
                <input type="hidden" name="supporting_document[]" value="{{$doc['id']}}">
                <?php
                array_push($docId, $doc['id']);
                ?>
            </div>
        @endforeach
    @endif
</div>


<div class="form-group">
    {!! Form::label('annexes_missing',trans('contract.annexes'),['class'=>'col-sm-2 control-label']) !!}
    <div class="col-sm-7">
        <?php
        $annexes_missing = isset($contract->metadata->annexes_missing) ? $contract->metadata->annexes_missing : - 1;
        ?>
        {!! Form::radio('annexes_missing', 1 ,($annexes_missing=='1') ? true : null , ['class' => 'field']) !!}
		{{trans('global.yes',[],null,$locale)}}
        {!! Form::radio('annexes_missing', 0 ,($annexes_missing=='0') ? true : null , ['class' => 'field']) !!}
		{{trans('global.no',[],null,$locale)}}
        {!! Form::radio('annexes_missing', -1,($annexes_missing=='-1') ? true : null, ['class' => 'field']) !!}
		{{trans('global.not_available',[],null,$locale)}}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'annexes_missing','metadata') !!}
    @endif
</div>

<div class="form-group">
    {!! Form::label('pages_missing',trans('contract.pages'),['class'=>'col-sm-2 control-label']) !!}
    <div class="col-sm-7">
        <?php
        $pages_missing = isset($contract->metadata->pages_missing) ? $contract->metadata->pages_missing : - 1;
        ?>
        {!! Form::radio('pages_missing', 1 ,($pages_missing=='1') ? true : null , ['class' => 'field']) !!}
		{{trans('global.yes',[],null,$locale)}}
        {!! Form::radio('pages_missing', 0 ,($pages_missing=='0') ? true : null , ['class' => 'field']) !!}
		{{trans('global.no',[],null,$locale)}}
        {!! Form::radio('pages_missing', -1,($pages_missing=='-1') ? true : null , ['class' => 'field']) !!}
		{{trans('global.not_available',[],null,$locale)}}
    </div>
    @if($action == 'edit')
        {!! discussion($discussions,$discussion_status, $contract->id,'pages_missing','metadata') !!}
    @endif

</div>

@if($action == 'edit')
    <input class="delete_company" type="hidden" name="delete[company]" value=""/>
    <input class="delete_government_entity" type="hidden" name="delete[government_entity]" value=""/>
    <input class="delete_concession" type="hidden" name="delete[concession]" value=""/>
@endif

<div class="form-action">
    <div class="col-sm-7 col-lg-offset-3">
        {!! Form::submit($action == 'edit' ? trans('contract.save_changes') : trans('contract.submit'),['class'=>'btn btn-lg btn-primary' , 'id' => 'Submit']) !!}
        <a style="margin-left: 10px;" class="btn btn-lg  btn-danger back" href="{{route('contract.select.type')}}">@lang('contract.cancel')</a>
    </div>
</div>

<div class="modal fade modal-comment" id="commentModel" tabindex="-1" role="dialog" aria-labelledby="commentModelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div style="padding: 40px;">@lang('annotation.loading') </div>
        </div>
    </div>
</div>

@include('contract.partials.form.contract_scripts')
