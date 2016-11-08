<div class="modal fade annotation-comment-modal" tabindex="-1" role="dialog"
     aria-labelledby="annotation-reject-modal"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(['route' => ['contract.annotations.status', $contract->id],
            'class'=>'suggestion-form']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">@lang('annotation.suggest_changes')</h4>
            </div>
            <div class="modal-body">
                {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                'style'=>'width:100%'])!!}
                {!!Form::hidden('type', 'annotation')!!}
                {!!Form::hidden('current-status', $annotationStatus)!!}
                {!!Form::hidden('status','', ['id'=>"status"])!!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">@lang('global.form.cancel')</button>
                <button type="submit"
                        class="btn btn-primary">@lang('global.form.ok')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>