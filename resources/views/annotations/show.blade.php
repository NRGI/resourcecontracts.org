<?php
use App\Nrgi\Entities\Contract\Annotation;
?>
@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">

@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> @lang('contract.annotation_list') <span>{{$contract->metadata->contract_name or $contract->metadata->project_title}}</span>   <a class="btn btn-default pull-right" href="{{route('contract.show', $contract->id)}}">Back</a> </div>

        <div class="view-wrapper" style="background: #F6F6F6">
            <div id="pagelist"></div>
            <div id="message" style="padding: 0px 16px"></div>
            <div class="document-wrap">
                <div class="left-document-wrap annotate">
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
                                                <a href="{{route('contract.annotations.list',$contract->id)}}?page={{$annotation->document_page_no}}">
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
                            @if ($status === Annotation::DRAFT)
                                {!!Form::open(['route'=>['contract.annotations.status', $contract->id], 'style'=>"display:inline",'method'=>'post'])!!}
                                {!!Form::hidden('state', 'completed')!!}
                                {!!Form::button(trans('Complete'), ['type'=>'submit','class'=>'btn btn-primary confirm','data-confirm'=>trans('Are you sure you want to marked complete these annotations ?')])!!}
                                {!!Form::close()!!}
                            @elseif($status === Annotation::COMPLETED)
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
            @lang('Annotation not created. Please create') <a style="font-size: 14px" href="{{route('contract.pages', ['id'=>$contract->id])}}?action=annotate">here</a></li>
        @endif
    </div>
@stop

@section('script')
    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.twbsPagination.js') }}"></script>
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

        var contract = {
            id: '{{$contract->id}}',
            filesBaseDir: '{{$contract->id}}',
            totalPages: '{{$contract->pages->count()}}',
            currentPage: '{{$page}}',
            getPdfLocation: function() { return "/data/{0}/pages/{1}.pdf".format(this.filesBaseDir, this.currentPage);},
            viewUrl: "{{route('contract.annotations.list', ['id'=>$contract->id])}}",
            textLoadAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}",
            textSaveAPI: "{{route('contract.page.store', ['id'=>$contract->id])}}",
            annotationAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}",
            getAction: function() {
                if(this.canEdit) return "action=edit";
                else if(this.canAnnotate) return "action=annotate";
                else return "";
            }
        };

        var textEditor = {
            init: function(contract) {
                this.contract = contract;
                this.textUpdated = false;
                var options = {theme: 'snow'};
                if(!this.contract.canEdit) {
                    options.readOnly = true;
                    $('#saveButton').hide();
                }

                this.editor = new Quill('#editor', options);
                this.editor.addModule('toolbar', {container: '#toolbar'});

                this.editor.on('text-change', function(delta, source) {
                    if (source == 'api') {
                        //none
                    } else if (source == 'user') {
                        this.textUpdated = true;
                    }
                });
            },
            load: function() {
                var reText = '';
                var that = this;
                $.ajax({
                    url: this.contract.textLoadAPI,
                    data: {'page': this.contract.currentPage},
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        that.editor.setHTML(response.message);
                    }
                });
            },
        };

        var pagination = {
            init: function(contract) {
                this.contract = contract;
            },
            show: function() {
                var that = this;
                $('#pagelist').twbsPagination({
                    totalPages: this.contract.totalPages,
                    visiblePages: 10,
                    startPage: this.contract.currentPage,
                    onPageClick: function (event, page) {
                        location.href = '{0}?{1}&page={2}'.format(that.contract.viewUrl, that.contract.getAction(), page);
                    }
                });
            }
        };

        var contractAnnotator = {
            init: function(contract) {
                this.contract = contract;
                var options = (contract.canAnnotate)?{readOnly: false}:{readOnly: true};
                this.content = $('.annotate').annotator(options);
                this.content.annotator('addPlugin', 'Tags');
                this.availableTags = {!! json_encode(trans("codelist/annotationTag.annotation_tags")) !!};
        },
        setup: function(page) {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                annotationData: {
                    'url': this.contract.annotationAPI,
                    'contract': this.contract.id,
                    'document_page_no': this.contract.currentPage
                },
                loadFromSearch: {
                    'url': this.contract.annotationAPI,
                    'contract': this.contract.id,
                    'document_page_no': this.contract.currentPage
                }
            });
            function split( val ) {
                return val.split( / \s*/ );
            }
            function extractLast( term ) {
                return split( term ).pop();
            }
            var availableTags = this.availableTags;
            this.content.data('annotator').plugins.Tags.input.autocomplete({
                source: function( request, response) {
                    // delegate back to autocomplete, but extract the last term
                    response( $.ui.autocomplete.filter(
                            availableTags, extractLast( request.term ) ) );
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ) {
                    var terms = split( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms.join( " " );
                    return false;
                }
            });
        },
        };

        jQuery(function ($) {
            textEditor.init(contract)
            textEditor.load();
            pagination.init(contract)
            pagination.show();
            contractAnnotator.init(contract);
            contractAnnotator.setup();
            $('#saveButton').click(function () {
                textEditor.save();
            });
        });

    </script>
@stop
