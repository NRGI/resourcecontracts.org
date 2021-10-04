@extends('layout.app')

<?php
$contract_processing_completed = \App\Nrgi\Entities\Contract\Contract::PROCESSING_COMPLETE;
$contract_processing_failed = \App\Nrgi\Entities\Contract\Contract::PROCESSING_FAILED;
$contract_processing_running = \App\Nrgi\Entities\Contract\Contract::PROCESSING_RUNNING;
$contract_processing_pipline = \App\Nrgi\Entities\Contract\Contract::PROCESSING_PIPELINE;
?>

@section('content')
	<div class="panel custom__panel">
		<div class="panel-heading">
			<div class="panel__title">{{$contract->title}}</div>
			@if($current_user->can('delete-contract'))
				<div class="panel__right-btn">
					{!!Form::open(['route'=>['contract.destroy', $contract->id], 'style'=>"display:inline",
					'method'=>'delete'])!!}
					{!!Form::button(trans('contract.delete'), ['type'=>'submit','class'=>'btn btn-danger confirm',
					'data-confirm'=>trans('contract.confirm_delete')])!!}
					{!!Form::close()!!}
				</div>
			@endif
		</div>

		<div class="panel-body">
			<div class="block block__with-table">
				<div class="clearfix table__header-section">
					<div class="action-btn pull-right">
						<a href="{{route('activitylog.index')}}?contract={{$contract->id}}" class="btn btn-default">
							@lang('activitylog.activitylog')
						</a>
						@if($status == $contract_processing_completed)
							<div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
										aria-haspopup="true" aria-expanded="false">
									@lang('global.download') <span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li><a class="name-value-wrap" target="_blank"
										   href="{{$contract->file_url}}">@lang('global.pdf')</a></li>
									@if($contract->word_file !='')
										<li><a class="name-value-wrap"
											   href="{{route('contract.download', $contract->id)}}">@lang('global.word')</a>
										</li>
									@endif
								</ul>
							</div>
						@endif
					</div>
					<div class="pull-left">
						<div class="clearfix">
							<a href="{{route('contract.edit', $contract->id)}}"
							   class="btn btn-default pull-left edit-btn">@lang('contract.edit_metadata')</a>
							@if($status == $contract_processing_completed)
								<div class="pull-left">
									<a href="{{route('contract.review', ['id'=>$contract->id])}}"
									   class="btn btn-default">
										@lang('contract.view_pages')
									</a>
									<a href="{{route('contract.annotate', ['id'=>$contract->id])}}"
									   class="btn btn-default">
										@lang('contract.annotate_contract')
									</a>
								</div>
							@elseif($status == $contract_processing_failed)
								<div class="status"><strong>@lang('contract.status')</strong>: @lang('Failed')
									(@lang('contract.fail_status', ['status'=>$contract->pdf_structure]))
								</div>
							@elseif($status== $contract_processing_running)
								<div class="status">
									<strong>@lang('contract.status')</strong>: @lang('contract.processing')</div>
							@elseif($status == $contract_processing_pipline)
								<div class="status">
									<strong>@lang('contract.status')</strong>: @lang('contract.pipeline')</div>
							@endif
						</div>
					</div>
					<div class="col-md-12 row">
						@if($status == $contract_processing_completed)
							@if($contract->pdf_structure != null)
								<ul style="margin-bottom: 20px">
									<li>
										<strong>@lang('contract.pdf_type')</strong></span> @lang('contract.'.$contract->pdf_structure)
									</li>
								</ul>
							@endif
						@endif
					</div>
				</div>
				@include('contract.state')
				<a class="btn btn-default" href="{{route('contract.comment.list',$contract->id)}}">
					@lang('contract.view_all')
				</a>
			</div>

			<div class="user-wrapper block block__user">
				<ul>
					<li class="name-value-wrap">
						<span class="name"><strong>@lang('contract.contracting_id'):</strong></span>
						<span class="value"> {{$contract->metadata->open_contracting_id or ''}}</span>
					</li>
					<li class="name-value-wrap">
						<span class="name"><strong>@lang('contract.created_by'):</strong></span>
						@if(isset($contract->created_user->name))
							<span class="value"> {{$contract->created_user->name}}  @lang('global.on') {{$contract->created_datetime->format('D M d, Y h:i A')}}
								(GMT)</span>
						@endif
					</li>

					@if(!is_null($contract->updated_user))
						<li class="name-value-wrap">
							<span class="name"><strong>@lang('contract.last_modified_by'):</strong></span>
                             <span class="value">
                                 {{$contract->updated_user->name}}
								 @lang('global.on') {{$contract->last_updated_datetime->format('D M d, Y h:i A')}} (GMT)
                             </span>
						</li>
					@endif
				</ul>
			</div>

			@include('contract.partials.form.language', ['view' => 'show'] )

			<div class="contract-wrapper block">
				<ul>
					<li class="name-value-wrap">
						<span class="name"> <strong>@lang('contract.contract_name'):</strong></span>
                    <span class="value">
                    {{$contract->metadata->contract_name or ''}}
						{!! discussion($discussions,$discussion_status, $contract->id,'contract_name','metadata') !!}
                    </span>
					</li>

					<li class="name-value-wrap">
                    <span class="name">
                        <strong>@lang('contract.contract_identifier'):</strong>
                    </span>
                    <span class="value">{{$contract->metadata->contract_identifier or ''}}
						{!! discussion($discussions,$discussion_status, $contract->id,'contract_identifier','metadata') !!}
                    </span>
					</li>

					@if(isset($contract->metadata->language))
						<li class="name-value-wrap">
                        <span class="name">
                            <strong>@lang('contract.language'):</strong>
                        </span>
                         <span class="value">
                            {{getLanguageName($contract->metadata->language, $locale)}}
							 [{{$contract->metadata->language}}]
							 {!! discussion($discussions,$discussion_status, $contract->id,'language','metadata') !!}
                        </span>
						</li>
					@endif

					@if(isset($contract->metadata->country->name))
						<li class="name-value-wrap">
                        <span class="name">
                            <strong>@lang('contract.country'):</strong>
                        </span>
                         <span class="value">{{_l('codelist/country.'.$contract->metadata->country->code, $locale)}}
							 [{{$contract->metadata->country->code or ''}}]
							 @if(isset($contract->metadata->amla_url) && !empty($contract->metadata->amla_url))
								 <a href="{{$contract->metadata->amla_url}}">{{trans('contract.amla',[],null,$locale)}}</a>
							 @endif
							 {!! discussion($discussions,$discussion_status, $contract->id,'country','metadata') !!}
                        </span>
						</li>
					@endif

					<li class="name-value-wrap">
						<span class="name"> <strong>@lang('contract.resource'): </strong></span>
                     <span class="value">
                        @if(is_array($contract->metadata->resource) && count($contract->metadata->resource)>0)
							 {{join(', ', array_map(function($v)use($resourceList){return _l($resourceList[$v]);},$contract->metadata->resource))}}
						 @endif
						 {!! discussion($discussions,$discussion_status, $contract->id,'resource','metadata') !!}
                    </span>
					</li>

					@if(isset($contract->metadata->government_entity))
						<div class="government-entity-wrap license-wrap">
							@foreach($contract->metadata->government_entity as $key => $governmentEntity)
								<li class="name-value-wrap">
									<span class="name"> <strong>@lang('contract.government_entity'):</strong></span>
                                <span class="value">
                                 {{$governmentEntity->entity or ''}}
									{!! discussion($discussions,$discussion_status, $contract->id,'entity-'.$key,'metadata') !!}
                                </span>
								</li>
								<li class="name-value-wrap">
                                <span class="name">
                                    <strong>@lang('contract.government_identifier'):</strong>
                                </span>
                                <span class="value">
                                    {{$governmentEntity->identifier or ''}}
									{!! discussion($discussions,$discussion_status, $contract->id,'identifier-'.$key,'metadata') !!}
                                </span>
								</li>
							@endforeach
						</div>
					@endif

					<li class="name-value-wrap">
						<span class="name"><strong>@lang('contract.document_type'):</strong></span>
                    <span class="value">{{ isset($documentTypeList[$contract->metadata->document_type])===TRUE? _l($documentTypeList[$contract->metadata->document_type]): "" }}
						{!! discussion($discussions,$discussion_status, $contract->id,'document_type','metadata') !!}
                    </span>
					</li>

					<li class="name-value-wrap">
                    <span class="name">
                    <strong>@lang('contract.type_of_contract'): </strong></span>
                     <span class="value">
                    @if(is_array($contract->metadata->type_of_contract) && count($contract->metadata->type_of_contract)>0)
							 {{join(', ', array_map(function($v)use($contractTypeList){return _l($contractTypeList[$v]);},
							$contract->metadata->type_of_contract))}}
						 @endif
						 {!! discussion($discussions,$discussion_status, $contract->id,'type_of_contract','metadata') !!}
                    </span>
					</li>

					<li class="name-value-wrap">
						<span class="name"><strong>@lang('contract.signature_date'):</strong></span>
                    <span class="value">
                        {{$contract->metadata->signature_date or ''}}
						{!! discussion($discussions,$discussion_status, $contract->id,'signature_date','metadata') !!}
                    </span>
					</li>

					<li class="name-value-wrap">
						<span class="name"> <strong>@lang('contract.signature_year'):</strong></span>
                     <span class="value">
                        {{$contract->metadata->signature_year or ''}}
						 {!! discussion($discussions,$discussion_status, $contract->id,'signature_year','metadata') !!}
                    </span>
					</li>

					<li class="name-value-wrap">
						<span class="name"><strong>@lang('contract.is_contract_signed'):</strong></span>
						@if(isset($contract->metadata->is_contract_signed) && $contract->metadata->is_contract_signed)
							<span class="value">
                            {{trans('contract.yes',[],null, $locale)}}
								{!! discussion($discussions,$discussion_status, $contract->id,'is_contract_signed','metadata') !!}
                        </span>
						@else
							<span class="value">
                            {{trans('contract.no',[],null, $locale)}}
								{!! discussion($discussions,$discussion_status, $contract->id,'is_contract_signed','metadata') !!}
                        </span>
						@endif
					</li>
				</ul>
			</div>

			@if(isset($contract->metadata->company))
				<?php $companies = $contract->metadata->company;?>
				@if(count($companies)>0)
					<div class="block">
						<h3>@lang('contract.company') </h3>
						@foreach($companies as $k => $v)
							<ul style="margin-bottom: 20px; border-bottom:1px solid #ccc; padding-bottom:20px; ">
								<li class="name-value-wrap">
									<span class="name"><strong>@lang('contract.company_name'):</strong></span>
                                            <span class="value">
                                            {{$v->name}}
												{!! discussion($discussions,$discussion_status, $contract->id,'name-'.$k,'metadata') !!}
                                            </span>
								</li>

								@if(isset($v->participation_share))
									<li class="name-value-wrap">
										<span class="name"> <strong>@lang('contract.participation_share')
												:</strong></span>
                                                 <span class="value">
                                                {{$v->participation_share}}
													 {!! discussion($discussions,$discussion_status, $contract->id,'participation_share-'.$k,'metadata') !!}
                                                </span>
									</li>
								@endif

								<li class="name-value-wrap">
										<span class="name"> <strong>@lang('contract.jurisdiction_of_incorporation')
												:</strong></span>
                                             <span class="value">
                                            {{@trans('codelist/country',[],null,$locale)[$v->jurisdiction_of_incorporation]}}
												 {!! discussion($discussions,$discussion_status, $contract->id,'jurisdiction_of_incorporation-'.$k,'metadata') !!}
                                            </span>
								</li>

								<li class="name-value-wrap">
									<span class="name"> <strong>@lang('contract.registry_agency'):</strong></span>
                                            <span class="value">
                                            {{$v->registration_agency}}
												{!! discussion($discussions,$discussion_status, $contract->id,'registration_agency-'.$k,'metadata') !!}
                                            </span>
								</li>

								<li class="name-value-wrap">
									<span class="name"><strong>@lang('contract.incorporation_date'):</strong></span>
                                            <span class="value">
                                            {{$v->company_founding_date}}
												{!! discussion($discussions,$discussion_status, $contract->id,'company_founding_date-'.$k,'metadata') !!}
                                            </span>
								</li>

								<li class="name-value-wrap">
									<span class="name"> <strong>@lang('contract.company_address'):</strong></span>
                                             <span class="value">
                                                {{$v->company_address}}
												 {!! discussion($discussions,$discussion_status, $contract->id,'company_address-'.$k,'metadata') !!}
                                            </span>
								</li>

								<li class="name-value-wrap">
									<span class="name"><strong>@lang('contract.company_number'):</strong></span>
                                             <span class="value">
                                            @if(isset($v->company_number)){{$v->company_number}}@endif
												 {!! discussion($discussions,$discussion_status, $contract->id,'company_number-'.$k,'metadata') !!}
                                            </span>
								</li>

								<li class="name-value-wrap">
										<span class="name"> <strong>@lang('contract.corporate_grouping')
												:</strong></span>
                                             <span class="value">
                                            @if(isset($v->parent_company)) {{$v->parent_company}}@endif
												 {!! discussion($discussions,$discussion_status, $contract->id,'parent_company-'.$k,'metadata') !!}
                                            </span>
								</li>
								<li class="name-value-wrap">
									<span class="name"> <strong>@lang('contract.open_corporate'):</strong></span>

                                             <span class="value">
                                                 @if(!empty($v->open_corporate_id)) <a target="_blank"
																					   href="{{$v->open_corporate_id}}">{{$v->open_corporate_id}}</a>@endif
												 {!! discussion($discussions,$discussion_status, $contract->id,'open_corporate_id-'.$k,'metadata') !!}
                                            </span>
								</li>
								@if(isset($v->operator))
									<li class="name-value-wrap">
										<span class="name"> <strong>@lang('contract.operator'):</strong></span>
                                                <span class="value">
                                                @if($v->operator==1)
														{{trans('global.yes',[],null, $locale)}}
													@elseif($v->operator==0)
														{{trans('global.no',[],null, $locale)}}
													@elseif($v->operator==-1)
														{{trans('global.not_available',[],null, $locale)}}
													@endif
													{!! discussion($discussions,$discussion_status, $contract->id,'operator-'.$k,'metadata') !!}
                                                </span>
									</li>
								@endif
							</ul>
						@endforeach
					</div>
				@endif
			@endif

			<div class="block">
				<h3>@lang('contract.license_and_project')</h3>
				<div class="project-wrapper">
					<ul>
						<li class="name-value-wrap">
							<span class="name">   <strong>@lang('contract.project_name'):</strong></span>
                             <span class="value">
                                {{$contract->metadata->project_title or ''}}
								 {!! discussion($discussions,$discussion_status, $contract->id,'project_title','metadata') !!}
                            </span>
						</li>
						<li class="name-value-wrap">
										<span class="name"> <strong>@lang('contract.project_identifier')
												:</strong></span>
                            <span class="value">
                                {{$contract->metadata->project_identifier or ''}}
								{!! discussion($discussions,$discussion_status, $contract->id,'project_identifier','metadata') !!}
                            </span>
						</li>
					</ul>
				</div>
				@if(isset($contract->metadata->concession))
					<div class="license-wrap">
						@foreach($contract->metadata->concession as $key => $concession)
							<ul>
								@if(isset($concession->license_name))
									<li class="name-value-wrap">
											<span class="name"> <strong>@lang('contract.license_name_only')
													:</strong></span>
                                     <span class="value">
                                    {{$concession->license_name}}
										 {!! discussion($discussions,$discussion_status, $contract->id,'license_name-'.$key,'metadata') !!}
                                    </span>
									</li>
								@endif
								@if(isset($concession->license_identifier))
									<li class="name-value-wrap">
											<span class="name">  <strong>@lang('contract.license_identifier_only')
													:</strong></span>
                                    <span class="value">{{$concession->license_identifier}}
										{!! discussion($discussions,$discussion_status, $contract->id,'license_identifier-'.$key,'metadata') !!}
                                    </span>
									</li>
								@endif
							</ul>
						@endforeach
					</div>
				@endif
			</div>

			<div class="block">
				<h3>@lang('contract.source')</h3>
				<ul>
					<li class="name-value-wrap">
						<span class="name"><strong>@lang('contract.source_url'):</strong></span>
                     <span class="value">
                        <a href="{{$contract->metadata->source_url}}">{{$contract->metadata->source_url}}</a>
						 {!! discussion($discussions,$discussion_status, $contract->id,'source_url','metadata') !!}
                    </span>
					</li>
					<li class="name-value-wrap">
						<span class="name"> <strong>@lang('contract.disclosure_mode'):</strong></span>
                     <span class="value">
                        {{_l('codelist/disclosure_mode.'.$contract->metadata->disclosure_mode, $locale) }}
						 {!! discussion($discussions,$discussion_status, $contract->id,'disclosure_mode','metadata') !!}
						 @if(isset($contract->metadata->disclosure_mode_text) && !empty($contract->metadata->disclosure_mode_text))
							 ( {{$contract->metadata->disclosure_mode_text}} )
						 @endif
                    </span>
					</li>
					<li class="name-value-wrap">
						<span class="name"> <strong>@lang('contract.date_of_retrieval'):</strong></span>
                     <span class="value">
                    {{$contract->metadata->date_retrieval}}
						 {!! discussion($discussions,$discussion_status, $contract->id,'date_retrieval','metadata') !!}
                    </span>
					</li>
					<li class="name-value-wrap">
						<span class="name""><strong>@lang('contract.category'):</strong></span>
                     <span class="value">
                    <?php $catConfig = config('metadata.category');?>
						 @if(isset($contract->metadata->category) && is_array($contract->metadata->category) && count($contract->metadata->category)>0)
							 <?php $cat = [];
							 foreach ($contract->metadata->category as $key):
								 $cat[] = $catConfig[$key];
							 endforeach;
							 ?>
							 {{join(', ', $cat)}}
						 @endif
						 {!! discussion($discussions,$discussion_status, $contract->id,'category','metadata') !!}
                    </span>
					</li>

					@if(in_array('olc' , $contract->metadata->category))
						<li class="name-value-wrap">
							<span class="name"> <strong>{{ trans('contract.deal_number') }}:</strong></span>
                         <span class="value">
                            @if(isset($contract->metadata->deal_number))
								 {{ $contract->metadata->deal_number }}
							 @endif
							 {!! discussion($discussions,$discussion_status, $contract->id,'deal_number','metadata') !!}
                        </span>
						</li>
						<li class="name-value-wrap">
							<span class="name"> <strong>{{ trans('contract.matrix_page') }}:</strong></span>
                         <span class="value">
                            @if(isset( $contract->metadata->matrix_page))
								 <a href="{{ $contract->metadata->matrix_page }}"
									target="_blank">{{$contract->metadata->matrix_page}}</a>
							 @endif
							 {!! discussion($discussions,$discussion_status, $contract->id,'matrix_page','metadata') !!}
                        </span>
						</li>
					@endif

					<li class="name-value-wrap">
						<span class="name"> <strong>{{trans('contract.contract_note')}}:</strong></span>
                     <span class="value">
                    @if(isset($contract->metadata->contract_note))
							 {{$contract->metadata->contract_note}}
						 @endif
						 {!! discussion($discussions,$discussion_status, $contract->id,'contract_note','metadata') !!}
                    </span>
					</li>
				</ul>
			</div>

			<div class="associated-wrapper block">
				<h3>@lang('contract.associated_contracts')</h3>

				@if(!empty($associatedContracts))
					@foreach($associatedContracts as $associatedContract)
						<ul>
							<li class="name-value-wrap">
								<a href="{{route('contract.show',$associatedContract['contract']['id'])}}">
									{{$associatedContract['contract']['contract_name']}}
									@if($associatedContract['parent'])
										(Main)
									@endif
								</a>
							</li>
						</ul>
					@endforeach
				@else
					<ul>
						<li> @lang('contract.no_documents')</li>
					</ul>
				@endif
			</div>

			<div class="block">
				@if(isset($contract->metadata->annexes_missing))
					<ul>
						<li class="name-value-wrap">
							<span class="name"> <strong>@lang('contract.annexes_display'):</strong></span>
                         <span class="value">
                            @if($contract->metadata->annexes_missing == 1)@lang('global.yes')
							 @elseif($contract->metadata->annexes_missing == 0)@lang('global.no')
							 @elseif($contract->metadata->annexes_missing == -1)@lang('global.not_available')
							 @endif
							 {!! discussion($discussions,$discussion_status, $contract->id,'annexes_missing','metadata') !!}
                        </span>
						</li>
					</ul>
				@endif
				@if(isset($contract->metadata->pages_missing))
					<ul>
						<li class="name-value-wrap">
							<span class="name"><strong>@lang('contract.pages_display'):</strong></span>
                         <span class="value">
                            @if( $contract->metadata->pages_missing == 1)@lang('global.yes')
							 @elseif($contract->metadata->pages_missing == 0)@lang('global.no')
							 @elseif($contract->metadata->annexes_missing == -1)@lang('global.not_available')
							 @endif
							 {!! discussion($discussions,$discussion_status, $contract->id,'pages_missing','metadata') !!}
                        </span>
						</li>
					</ul>
				@endif
			</div>
		</div>
		@include('contract.partials.show.annotation_list')
	</div>
@stop

@include('contract.partials.show.script')

