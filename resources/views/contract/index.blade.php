@extends('layout.app')

@section('content')
	<div class="panel panel-default">
		<div class="panel-heading">@lang('contract.all_contract')
			<div class="pull-right" role="group" aria-label="...">
				<?php
				$url = Request::all();
				$url['download'] = 1;
				?>
				@if(!empty($download_files))
					<div class="btn-group">
						<a href="#" class="btn btn-default
                        dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							@lang('global.text_download') <span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							@foreach($download_files as $key => $file)
								<li>
									<a href="{{route('bulk.text.download',['file' => $file['path']])}}">
										{{$key}}
										<div>
											<small>({{$file['size']}})</small>
										</div>
									</a>
								</li>
								@if(in_array($key,['OLC', 'All']))
									<li role="separator" class="divider"></li>
								@endif
							@endforeach
							<li role="separator" class="divider"></li>
							<li>
								<small style="padding: 10px;">Updated on {{$file['date']}}</small>
							</li>
						</ul>
					</div>
				@endif
				<a href="{{route("contract.index",$url)}}" class="btn btn-info">@lang('contract.download')</a>
				<a href="{{route('contract.import')}}" class="btn btn-default">@lang('contract.import.name')</a>
				<a href="{{route('contract.select.type')}}" class="btn btn-primary btn-import">@lang('contract.add')</a>
			</div>
		</div>

		<div class="panel-body contract-filter">

			{!! Form::open(['route' => 'contract.index', 'method' => 'get', 'class'=>'form-inline']) !!}
			<div class="row">
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('year', ['all'=>trans('contract.year')] + $years , Request::input('year') , ['class' =>
					'form-control']) !!}
				</div>
				<div class="col-md-4 col-sm-6 col-xs-6">
				{!! Form::text('publishing_year_date_range', Request::input('publishing_year_date_range') , ['class' =>'form-control date-range-picker-input', 'id' => 'publishing_year_date_range', 'placeholder'=>trans('contract.date')]) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('country', ['all'=>trans('contract.country')] + $countries , Request::input('country') ,
					['class' =>'form-control']) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('category', ['all'=>trans('contract.category')] + config('metadata.category'),
					Request::input('category') ,
					['class' =>'form-control']) !!}
				</div>
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('resource', ['all'=>trans('contract.resource')] + $resourceList ,
					Request::input
					('resource') ,
					['class' =>'form-control']) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('document_type', ['all'=>trans('contract.document_type')] + $documentTypeList,
					Request::input('document_type') ,
					['class' =>'form-control']) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('type_of_contract', ['all'=>trans('contract.type_of_contract')] + $contractTypeList,
					Request::input('type_of_contract') ,
					['class' =>'form-control']) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('company_name', ['all'=>trans('contract.company_name')] + $companyNamesList,
					Request::input('company_name') ,
					['class' =>'form-control']) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('language', ['all'=>trans('contract.language')] + [''=>trans('codelist/language',[],null,$locale)['major'],
					'Other'=>trans('codelist/language',[],null,$locale)['minor']],
					Request::input('language') ,
					['class' =>'form-control']) !!}
				</div>
				<div  class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::text('q', Request::input('q') , ['class' =>'form-control','placeholder'=>trans('contract.search_contract')]) !!}
				</div>
			</div>
			<div style = "display: flex;">
			{!! Form::submit(trans('contract.search'), ['class' => 'btn btn-primary']) !!}
			<a class="btn btn-primary" style="margin-left: 10px"  href="{{ url('/contract') }}">{{trans('contract.reset')}}</a>
			</div>
			{!! Form::close() !!}
			<br/>
			<br/>
			<table class="table table-contract table-responsive contract-table">
				@forelse($contracts as $contract)
					<tr>
						<td width="65%">
							<i class="glyphicon glyphicon-file"></i>
							<a href="{{route('contract.show', ['contract' => $contract->id])}}"
							   class="contract-title">{{$contract->metadata->contract_name ?? $contract->metadata->project_title}}</a>
							<span class="label label-default">
								<?php echo strtoupper(
										$contract->metadata->language
								);?>
							</span>
							@if($contract->metadata_status == \App\Nrgi\Entities\Contract\Contract::STATUS_PUBLISHED)
								<span class="published">
								<i class="glyphicon glyphicon-ok"></i>
									@lang('contract.metadata_published')
							</span>
							@endif
							@if($contract->text_status == \App\Nrgi\Entities\Contract\Contract::STATUS_PUBLISHED)
								<span class="published">
								<i class="glyphicon glyphicon-ok"></i>
									@lang('contract.text_published')
							</span>
							@endif
							@if(isset($annotationStatusArray) && isset($annotationStatusArray[$contract->id]) && $annotationStatusArray[$contract->id]  == \App\Nrgi\Entities\Contract\Contract::STATUS_PUBLISHED)
								<span class="published">
								<i class="glyphicon glyphicon-ok"></i>
									@lang('contract.annotation_published')
							</span>
							@endif
							<div class="contract-info-list">
								<span class="info">
									<i class="glyphicon glyphicon-time"></i>
									{{$contract->metadata->signature_year}}
								</span>
								<span class="info">
									<i class="glyphicon glyphicon glyphicon-map-marker"></i>
									{{$contract->metadata->country->name}}
								</span>
								<span class="info">
									<i class="glyphicon glyphicon-comment"></i>
									{{$contract->annotations->count()}}
								</span>
							</div>
						</td>
						<td align="right">
							<div class="contract-extra-details">
								<span>{{getFileSize($contract->metadata->file_size)}}</span>
								<span><?php echo $contract->createdDate('M d, Y');?></span>
							</div>
							<div class="contract-metadata-lang">
								@lang('contract.translation_available_in')
								<span class="index-lang">
									@include('contract.partials.form.language', ['view' => 'show', 'page'=>'index'] )
								</span>
							</div>
						</td>
					</tr>

				@empty
					<tr>
						<td colspan="2">@lang('contract.contract_not_found')</td>
					</tr>
				@endforelse

			</table>
			@if ($contracts->lastPage()>1)
				<div class="text-center paginate-wrapper">
					<div class="pagination-text">@lang('contract.showing') {{($contracts->currentPage()==1)?"1":($contracts->currentPage()-1)*$contracts->perPage()}} @lang('contract.to') {{($contracts->currentPage()== $contracts->lastPage())?$contracts->total():($contracts->currentPage())*$contracts->perPage()}} @lang('contract.of') {{$contracts->total()}} @lang('contract.contract')</div>
					{!! $contracts->appends($app->request->all())->render() !!}
				</div>
			@endif
		</div>
	</div>
@endsection
@section('script')
	<link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
	<link href="{{asset('css/daterangepicker.css')}}" rel="stylesheet"/>
	<script src="{{asset('js/select2.min.js')}}"></script>
	<script src="{{asset('js/moment.min.js')}}"></script>
	<script src="{{asset('js/daterangepicker.min.js')}}"></script>
	<script type="text/javascript">
		var lang_select = '@lang('global.select')';
		$('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
	</script>
	<script>
		$(function() {
			$( document ).ready(function() {
				var urlParams = new URLSearchParams(window.location.search);
				var dateRange = urlParams.get('publishing_year_date_range');
				let dateFormat = 'YYYY-MM-DD';
				let minDate = new Date()
				let maxDate = new Date();
				if(dateRange) {
					let dateArray = dateRange.split('to').map(v => v.trim());
					if(dateArray.length === 2 && moment(dateArray[0], dateFormat).isValid() && moment(dateArray[1], dateFormat).isValid()) {
						minDate = moment(dateArray[0], dateFormat).toDate();
						maxDate = moment(dateArray[1], dateFormat).toDate();
					}
				}
				$('input[name="publishing_year_date_range"]').daterangepicker({
					opens: 'left',
					startDate: minDate,
					endDate: maxDate,
					autoUpdateInput: false,
					locale: {
						cancelLabel: 'Clear',
					}
				});

				$('input[name="publishing_year_date_range"]').on('apply.daterangepicker', function(ev, picker) {
					$(this).val(picker.startDate.format(dateFormat) + ' to ' + picker.endDate.format(dateFormat));
				});
				$('input[name="publishing_year_date_range"]').on('cancel.daterangepicker', function(ev, picker) {
					$(this).val('');
				});
		});
});
	</script>
@stop