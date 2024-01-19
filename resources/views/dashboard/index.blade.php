@extends('layout.app')
@section('css')
	<style>
		.balance {
			padding: 10px;
			background-color: #fff;
			margin-bottom: 20px;
		}
		.abby-balance {
			margin-bottom: 0px;
		}

		.contract-list .media {
			margin-bottom: 20px;
		}

		.row-dashboard ul {
			padding: 0px;
		}
	</style>
@stop
<?php app()->getLocale();?>

@section('content')
	<div class="row">
	<div class="panel-default panel-dashboard">
		<div class="row dashboard-top-wrapper">
			<div class="col-md-12"><h3>@lang('global.contracts_uploaded')</h3></div>
			<div class="col-sm-10 col-lg-10">
				<div class="row">
				<div class="contract-upload-wrap">
					<div class="col-md-3 col-sm-6 col-xs-12">
						<div class="inner__block">
							<h4>@lang('global.last_month')</h4>
							<span class="text-muted">{{$stats['last_month'] ?? 0}}</span>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-12">
						<div class="inner__block">
							<h4>@lang('global.this_month')</h4>
							<span class="text-muted">{{$stats['this_month'] ?? 0}}</span>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-12">
						<div class="inner__block">
							<h4>@lang('global.yesterday')</h4>
							<span class="text-muted">{{$stats['yesterday'] ?? 0}}</span>
						</div>
					</div>
					<div class="col-md-3 col-sm-6 col-xs-12">
						<div class="inner__block">
							<h4>@lang('global.today')</h4>
							<span class="text-muted">{{$stats['today'] ?? 0}}</span>
						</div>
					</div>
				</div>
				</div>
			</div>
			<div class="col-sm-2 col-lg-2">
				<div class="inner__block">
				<h4>@lang('global.total_contracts')</h4>
				<span class="text-muted">{{$stats['total'] ?? 0}}</span>
					</div>
				</div>

		</div>
	</div>

		<div class="col-md-12">
			<div class="balance"><p>@lang('global.mechanical_turk_balance'): {{$stats['balance'] ?? '-'}}</p>
				@if(isset($stats['abby_info']))
					<p class="abby-balance">@lang('global.abby_balance'): {{$stats['abby_info']['pages'] ?? '-'}} @lang('global.pages')</p>
				@endif
			</div>
		</div>
	<div class="clearfix row-dashboard">
		<div class="col-md-3 col-sm-6 col-xs-12">
			<div class="inner__block">
				<h4>@lang('global.metadata_status')</h4>
				<ul>
					<li>
						<a href="{{route('contract.index',["type"=>"metadata","status"=>"published"])}}">@lang('global.published')
							:</a> <span class="number number-published">{{$status['metadata']['published'] ?? 0}}</span>
					</li>
					<li>
						<a href="{{route('contract.index',["type"=>"metadata","status"=>"completed"])}}">@lang('global.completed')
							:</a> <span class="number number-completed">{{$status['metadata']['completed'] ?? 0}}</span>
					</li>
					<li>
						<a href="{{route('contract.index',["type"=>"metadata","status"=>"draft"])}}">@lang('global.draft')
							:</a> <span class="number number-draft">{{$status['metadata']['draft'] ?? 0}}</span>
					</li>
					<li>
						<a href="{{route('contract.index',["type"=>"metadata","status"=>"rejected"])}}">@lang('mturk.rejected')
							:</a> <span class="number number-rejected">{{$status['metadata']['rejected'] ?? 0}}</span>
					</li>
				</ul>
			</div>
		</div>

		<div class="col-md-3 col-sm-6 col-xs-12">
			<div class="inner__block">
			<h4>@lang('global.annotations_status')</h4>

			<ul>
				<li>
					<a href="{{route('contract.index',["type"=>"annotations","status"=>"published"])}}">@lang('global.published')
						:</a> <span class="number number-published">{{$status['annotation']['published'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"annotations","status"=>"completed"])}}">@lang('global.completed')
						:</a><span class="number number-completed">{{$status['annotation']['completed'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"annotations","status"=>"draft"])}}">@lang('global.draft')
						: </a><span class="number number-draft">{{$status['annotation']['draft'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"annotations","status"=>"rejected"])}}">@lang('mturk.rejected')
						:</a> <span class="number number-rejected">{{$status['annotation']['rejected'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"annotations","status"=>"processing"])}}">@lang('global.not_available')
						:</a> <span
							class="number number-published">{{$status['annotation']['processing'] ?? 0}}</span>
				</li>
			</ul>
				</div>
		</div>

		<div class="col-md-3 col-sm-6 col-xs-12">
			<div class="inner__block">
			<h4>@lang('global.pdf_text_status')</h4>
			<ul>
				<li>
					<a href="{{route('contract.index',["type"=>"pdftext","status"=>"published"])}}">@lang('global.published')
						: </a><span class="number number-completed">{{$status['pdfText']['published'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"pdftext","status"=>"completed"])}}">@lang('global.completed')
						: </a><span class="number number-completed">{{$status['pdfText']['completed'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"pdftext","status"=>"draft"])}}">@lang('global.draft')
						:</a> <span class="number number-draft">{{$status['pdfText']['draft'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"pdftext","status"=>"rejected"])}}">@lang('mturk.rejected')
						:</a> <span class="number number-rejected">{{$status['pdfText']['rejected'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"pdftext","status"=>"null"])}}"> @lang('contract.on_process')
						: </a><span
							class="number number-published">{{$status['pdfText']['processing'] ?? 0}}</span>
				</li>
			</ul>
				</div>
		</div>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<div class="inner__block">
			<h4>@lang('global.ocr')</h4>
			<ul>
				<li>
					<a href="{{route('contract.index',["type"=>"ocr","status"=>"1"])}}">@lang('contract.acceptable')
						:</a> <span class="number number-published">{{$ocrStatusCount['acceptable'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"ocr","status"=>"2"])}}">@lang('contract.needs_editing')
						:</a> <span class="number number-completed">{{$ocrStatusCount['editing'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"ocr","status"=>"3"])}}">@lang('contract.needs_full_transcription')
						:</a> <span class="number number-draft">{{$ocrStatusCount['transcription'] ?? 0}}</span>
				</li>
				<li>
					<a href="{{route('contract.index',["type"=>"ocr","status"=>"null"])}}">@lang('global.not_available')
						:</a> <span class="number number-draft">{{$ocrStatusCount['non'] ?? 0}}</span>
				</li>

			</ul>
		</div>
			</div>
	</div>


	<div class="col-lg-12">
		<div class="block dashboard__block">
		<div class="page-header">
			<h3>@lang('global.most_recent_contracts')</h3>
		</div>
		<table class="table table-responsive table-dashboard">
			@forelse($recent_contracts as $contract)
				<tr>
					<td>
						<h5 class="media-heading user_name">
							{{ $contract->metadata->contract_name ?? '' }}
							@if(isset($contract->metadata->countries) && is_array($contract->metadata->countries))
								@foreach($contract->metadata->countries as $country)
									, {{ $country->name ?? '' }}@if(!$loop->last),@endif
								@endforeach
							@endif
							, {{ $contract->metadata->signature_year ?? '' }}
						</h5>
						<span>- {{ $contract->created_user->name }}</span>
					</td>
					<td style="width: 250px;text-align: right;">
						<small><?php echo $contract->createdDate('F d, Y \a\t H:i A');?></small>
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="2">@lang('global.no_contracts_created')</td>
				</tr>
			@endforelse
		</table>

		</div>
	</div>
	</div>
@stop
