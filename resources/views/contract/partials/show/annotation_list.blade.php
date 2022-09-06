@if($status == $contract_processing_completed)
	<div class="annotation-wrap block" id="annotations">
		<h3>@if($annotations->count() > 1)
				<span class="annotation-count">{{$annotations->count()}}</span>
			@endif
			@lang('contract.annotations')
			<span class="annotation-help">@lang('annotation.edit')</span></h3>

		<div class="annotation-list">
			<ul>
				@forelse($annotations as $key => $annotation)
					<?php
					$annotation->setLang($locale);
					?>
					<li>
						<div class="row">
							<div class="col-xs-8 col-sm-8 col-md-9">
								<p>{{_l("codelist/annotation.annotation_category.{$annotation->category}", $locale)}}</p>
                                <span data-pk="{{$annotation->id}}"
									  data-name="text"
									  data-params='{"lang" : "{{$locale}}" }'
									  data-url="{{route('annotation.update')}}"
									  data-type="textarea"
									  class="edit-annotation-text">{!!nl2br($annotation->text)!!}</span>
							</div>
							<div style="padding: 20px;">
								{!! discussion($discussions,$discussion_status, $contract->id,$annotation->id,'annotation') !!}
							</div>
						</div>
						<?php
						$childs = $annotation->child->sortBy('page_no');
						?>
						@foreach($childs as $child)
							<?php
							$child->setLang($locale);
							?>
							<div class="row">
								<div class="col-md-10">
									@if(property_exists($child->annotation, "shapes"))
										<span class="annotation-type-icon annotation-pdf-icon"></span>
									@else
										<span class="annotation-type-icon annotation-text-icon"></span>
									@endif
									@if(property_exists($child->annotation, "shapes"))
										<a href="{{route('contract.annotate',$contract->id)}}#/pdf/page/{{$child->page_no}}/annotation/{{$child->id}}"> @lang('annotation.page') </a>
										<span data-pk="{{$child->id}}"
											  data-name="page_no"
											  data-url="{{route('annotation.update')}}"
											  data-value="{{$child->page_no}}"
											  data-type="select"
											  class="edit-annotation-page">{{$child->page_no}}</span>
									@else
										<a href="{{route('contract.annotate',$contract->id)}}#/text/page/{{$child->page_no}}/annotation/{{$child->id}}">  @lang('annotation.page') </a>
										<span>{{$child->page_no}}</span>
									@endif

									@if(!empty($child->article_reference))
										<span>-</span>
										<span data-pk="{{$child->id}}"
											  data-params='{"lang" : "{{$locale}}" }'
											  data-name="article_reference"
											  data-url="{{route('annotation.update')}}" data-type="text"
											  class="edit-annotation-section"> {{$child->article_reference ?? ''}}</span>
									@endif
								</div>
								<div class="col-md-2">
									<a href="javascript:void(0)" data-pk="{{$child->id}}"
									   class="annotation-delete-btn">@lang('annotation.delete')</a></div>
							</div>
						@endforeach
					</li>
				@empty
					<li>
						@lang('annotation.not_created')
						<a style="font-size: 14px"
						   href="{{route('contract.annotate', ['id'=>$contract->id])}}">@lang('annotation.here')</a>
					</li>
				@endforelse
			</ul>
		</div>
	</div>
@endif