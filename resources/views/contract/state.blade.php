<?php
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\Contract\Annotation;

?>
<div class="state-wrap">
    <p> @lang('Contract State'):</p>
    <ul>
        <li>
            @include('contract.partials.show.metadata_status')
        </li>
        @if($status == $contract_processing_completed)
            <li>
                @include('contract.partials.show.text_status')
            </li>
            <li>
                <strong>@lang('Annotations'):</strong>
                @if (count($annotations) > 0)
                    @include('contract.partials.show.annotation_status')
                @endif
                <a style="padding-left: 10px"
                   href="{{route('contract.pages', ['id'=>$contract->id])}}?action=annotate">@lang('contract.annotate')</a>
            </li>

        @else
            <li><strong>@lang('Text'):</strong></li>
            <li><strong>@lang('Annotation'):</strong></li>
        @endif
    </ul>
</div>
