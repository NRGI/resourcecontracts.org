<?php
use App\Nrgi\Entities\Contract\Annotation;
?>
@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading"> @lang('contract.annotation_list') <span>{{$contract->metadata->contract_name ?? $contract->metadata->project_title}}</span>   <a class="btn btn-default pull-right" href="{{route('contract.show',['contract' => $contract->id])}}">Back</a> </div>

        <div class="view-wrapper" style="background: #F6F6F6">
            <div id="pagination"></div>
            <div id="message" style="padding: 0px 16px"></div>
            <div class="document-wrap">
                <div class="left-document-wrap" id="annotatorjs">
                    <div class="quill-wrapper">
                        <!-- Create the toolbar container -->
                        <div id="toolbar" class="ql-toolbar ql-snow">
                        </div>
                        <div id="editor" style="height: 750px" class="editor ql-container ql-snow">
                            <div class="ql-editor" id="ql-editor-1" contenteditable="true">

                            </div>
                            <div class="ql-paste-manager" contenteditable="true"></div>
                        </div>

                    </div>
                </div>
                <div class="right-document-wrap">
                    @if(count($contract->annotations) > 0)
                        <div class="annotation-wrap">
                            <h3>@lang('contract.annotations')
                                <div class="action-btn pull-right">
                                    <div class="annotation-status"><span class="{{$status}}">@lang(ucfirst($status))</span> </div>
                                </div>
                            </h3>
                            <div class="annotation-list">

                                <ul>
                                    @forelse($contract->annotations as $annotation)
                                        <li>
                                            <span>
                                                <a href="{{route('contract.annotations.list',['key' => $contract->id])}}?page={{$annotation->document_page_no}}">
                                                    {{$annotation->annotation->quote}}
                                                </a>
                                                [Page {{$annotation->document_page_no}}]
                                            </span>
                                            <p>{{$annotation->annotation->text}}</p>
                                            @foreach($annotation->annotation->tags as $tag)
                                                <div>{{$tag}}</div>
                                            @endforeach
                                        </li>
                                    @empty
                                    @endforelse
                                </ul>
                            </div>
                            @if ($status === Annotation::DRAFT and $current_user->can('complete-annotation'))
                                {!!Form::open(['route'=>['contract.annotations.status', $contract->id], 'style'=>"display:inline",'method'=>'post'])!!}
                                {!!Form::hidden('state', 'completed')!!}
                                {!!Form::button(trans('Complete'), ['type'=>'submit','class'=>'btn btn-primary confirm','data-confirm'=>trans('Are you sure you want to marked complete these annotations ?')])!!}
                                {!!Form::close()!!}
                            @elseif($status === Annotation::COMPLETED and ($current_user->can('publish-annotation') ?? $current_user->can('reject-annotation') ))
                                {!!Form::open(['route'=>['contract.annotations.status', $contract->id], 'style'=>"display:inline",'method'=>'post'])!!}
                                {!!Form::hidden('state', 'published')!!}
                                {!!Form::button(trans('Publish'), ['type'=>'submit','class'=>'btn btn-success confirm','data-confirm'=>trans('Are you sure you want to marked publish these annotations ?')])!!}
                                {!!Form::close()!!}

                                <button data-toggle="modal" data-target="#reject-it-modal" class="btn btn-danger">{{trans('Reject')}}</button>

                                <div class="modal fade" id="reject-it-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                     aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            {!! Form::open(['route' => ['contract.annotations.comment', $contract->id],
                                            'class'=>'suggestion-form']) !!}
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                            aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="myModalLabel">@lang('Suggest changes for annotations')</h4>
                                            </div>
                                            <div class="modal-body">
                                                {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12, 'style'=>'width:100%'])!!}
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default"
                                                        data-dismiss="modal">@lang('global.form.cancel')</button>
                                                <button type="submit" class="btn btn-primary">@lang('global.form.ok')</button>
                                            </div>
                                            {!! Form::close() !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                </div>
            </div>
        </div>

        @else
            @lang('Annotation not created. Please create') <a style="font-size: 14px" href="{{route('contract.annotate', ['id'=>$contract->id])}}?action=annotate">here</a></li>
        @endif
    </div>
@stop

@section('script')
    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.simplePagination.js') }}"></script>
    <script src="{{ asset('js/lib/underscore.js') }}"></script>
    <script src="{{ asset('js/lib/backbone.js') }}"></script>
    <script src="{{ asset('js/contractmvc.js') }}"></script>
    <script>
        //defining format to use .format function
        String.prototype.format = function () {
            var formatted = this;
            for (var i = 0; i < arguments.length; i++) {
                var regexp = new RegExp('\\{' + i + '\\}', 'gi');
                formatted = formatted.replace(regexp, arguments[i]);
            }
            return formatted;
        };

        var contract = new Contract({
            id: '{{$contract->id}}',
            totalPages: '{{$contract->pages->count()}}',
            currentPage: '{{$page}}',
            
            editorEl: '#editor',
            paginationEl: '#pagination',
            // annotationEl: '#annotation',
            // pdfviewEl: 'pdfcanvas',
            annotatorjsEl: '#annotatorjs',

            textLoadAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}",  
            textSaveAPI: "{{route('contract.page.store', ['id'=>$contract->id])}}",

            annotatorjsAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}"
        });

        var pageView = new PageView({
            pageModel: contract.getPageModel(),
            paginationView: new PaginationView({
                paginationEl: contract.getPaginationEl(), 
                totalPages: contract.getTotalPages(), 
                pageModel: contract.getPageModel()
            }),
            textEditorView: new TextEditorView({
                editorEl: contract.getEditorEl(), 
                pageModel: contract.getPageModel()
            }),
            annotatorjsView: new AnnotatorjsView({
                annotatorjsEl: contract.getAnnotatorjsEl(),
                pageModel: contract.getPageModel(),
                contractModel: contract,
                tags:{!! json_encode(trans("codelist/annotationTag.annotation_tags")) !!}
            }),            
        }).render();        

    </script>
@stop
