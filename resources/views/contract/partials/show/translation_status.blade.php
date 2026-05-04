<?php
use App\Nrgi\Entities\Contract\Contract;

# translation_status holds either a Lambda lifecycle state (PENDING /
# IN_PROGRESS / COMPLETED / FAILED) or an editorial publish state (published /
# unpublished). The publish row only renders controls once the Lambda has
# finished — i.e., status is COMPLETED, published, or unpublished.
$translationStatus = $contract->translation_status;
$publishableStates = [
    Contract::TRANSLATION_COMPLETED,
    Contract::STATUS_PUBLISHED,
    Contract::STATUS_UNPUBLISHED,
];
$isPublishable = in_array($translationStatus, $publishableStates, true);
?>

@if($status == $contract_processing_completed)
	<td>
		@if($translationStatus == Contract::STATUS_PUBLISHED)
			<span class="state published">@lang('global.published')</span>
		@elseif($translationStatus == Contract::STATUS_UNPUBLISHED)
			<span class="state draft">@lang('contract.translation_status_unpublished')</span>
		@elseif($translationStatus == Contract::TRANSLATION_COMPLETED)
			<span class="state draft">@lang('global.draft')</span>
		@else
			<span class="state draft">
				@lang('contract.translation_status_' . strtolower($translationStatus ?? 'not_started'))
			</span>
		@endif
	</td>

	<td></td>

	<td>
		@if($isPublishable && $elementState['metadata']=='published' && $elementState['text']=='published')
			@if($translationStatus != Contract::STATUS_PUBLISHED)
				{!! Form::open(['route' => ['contract.status.comment', $contract->id],
				'class'=>'suggestion-form pull-left']) !!}
				{!!Form::hidden('type', 'translation',[])!!}
				{!!Form::hidden('status', 'published' , [])!!}
				<button type="submit"
						class="btn btn-success metadata-status-comment">@lang("global.publish")</button>
				{!! Form::close() !!}
			@endif
		@endif
	</td>

	<td>
		<?php
		$link = "http://".env('RC_LINK')."/contract/".$contract->metadata->open_contracting_id."/view#text";
		if (in_array('olc', $contract->metadata->category)) {
			$link = "http://".env('OLC_LINK')."/contract/".$contract->metadata->open_contracting_id."/view#text";
		}
		?>
		@if($translationStatus == Contract::STATUS_PUBLISHED)
			@lang('contract.translation_pages_translated'):
			{{ $contract->translated_pages_count }}/{{ $contract->total_translatable_pages_count }}
			<a href="{{$link}}" target="_blank"><span class="glyphicon glyphicon-link"
													  title="@lang('global.check_text_in_subsite')"></span></a>

			{!! Form::open(['route' => ['contract.status.comment', $contract->id],'style'=>"display:inline-block", 'class'=>'suggestion-form ']) !!}
				{!!Form::hidden('type', 'translation',[])!!}
				{!!Form::hidden('status', 'unpublished' , [])!!}
				<button type="submit"
						class="btn btn-danger metadata-status-comment">@lang("global.unpublish")</button>
			 {!! Form::close() !!}
		@else
			-
		@endif
	</td>
@else
	<td></td>
	<td></td>
	<td></td>
	<td></td>
@endif
