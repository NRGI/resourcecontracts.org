<?php
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\Contract\Annotation;

?>
<div class="state-wrap">
    <div class="state-wrap-title">
    <p style="float: left"> @lang('contract.contract_state'):
        @if(!$current_user->isCountryResearch())
            {!!Form::open(['route'=>['contract.unpublish', $contract->id], 'style'=>"float:right;  padding-left: 10px;",
            'method'=>'post'])!!}
            {!!Form::button(trans('contract.unpublish.all'), ['type'=>'submit','class'=>'btn btn-danger btn-sm confirm',
            'data-confirm'=>trans('contract.unpublish.confirm')])!!}
            {!!Form::close()!!}

            {!!Form::open(['route'=>['contract.publish', $contract->id], 'style'=>"float:right",
            'method'=>'post'])!!}
            {!!Form::button(trans('contract.publish.all'), ['type'=>'submit','class'=>'btn btn-success btn-sm confirm',
            'data-confirm'=>trans('contract.publish.confirm')])!!}
            {!!Form::close()!!}
        @endif
    </p>
    </div>

    <ul>
        <li>
            @include('contract.partials.show.metadata_status')
        </li>
        @if($status == $contract_processing_completed)
            <li>
                @include('contract.partials.show.text_status')
            </li>
            <li>
                <strong>@lang('annotation.annotations'):</strong>
                @if (count($annotations) > 0)
                    @include('contract.partials.show.annotation_status')
                @endif
                <a style="padding-left: 10px"
                   href="{{route('contract.annotate', ['id'=>$contract->id])}}">@lang('contract.annotate')</a>
            </li>

        @else
            <li><strong>@lang('global.text'):</strong></li>
            <li><strong>@lang('annotation.annotation'):</strong></li>
        @endif
    </ul>
</div>
