@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">

@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> @lang('contract.editing') <span>{{$contract->metadata->contract_name or $contract->metadata->project_title}}</span>   <a class="btn btn-default pull-right" href="{{route('contract.show', $contract->id)}}">Back</a> </div>

        <div class="view-wrapper" style="background: #F6F6F6">
            <a class="btn btn-default pull-right" href="{{route('contract.annotations.list', $contract->id)}}">Annotations</a>

            <div id="pagelist"></div>
             <div id="message" style="padding: 0px 16px"></div>
            <div class="document-wrap">
            <div class="left-document-wrap annotate_left">
                    <div class="quill-wrapper">
                        <div id="pagelist_left"></div>
                        <div id="editor_left" class="editor">
                        </div>
                    </div>
                </div>
                <div class="right-document-wrap annotate_right">
                    <div class="quill-wrapper">
                        <div id="pagelist_right"></div>
                        <div id="editor_right" class="editor">
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

    function Contract(options) {
        return {
            id: options.id,
            totalPages: options.totalPages,
            currentPage: options.currentPage,
            init: function() {
                this.editorEl = "#editor_{0}".format(options.position);
                this.editor = new TextEditor(this.editorEl).init();
                this.annotator = new ContractAnnotator(".annotate_{0}".format(options.position), this.id, options.annotationAPI).init();
                return this;
            },
            loadPageText: function(page) {
                this.currentPage = page;
                this.editor.load(options.textLoadAPI, this.currentPage);
                return this;
            },
            loadPagination: function() {
                new Pagination("#pagelist_{0}".format(options.position), this).show();
                return this;
            },
            loadPageAnnotations: function(page) {
                this.annotator.load(page);
                return this;
            }
        };
    };

    function TextEditor(el) {
        return {
            init: function() {
                var options = {theme: 'snow'};
                this.editor = new Quill(el, options);
                return this;
            },
            load: function(api, currentPage, callback) {
                var reText = '';
                var that = this;
                $.ajax({
                    url: api,
                    data: {'page': currentPage},
                    type: 'GET',
                    async: false,
                    success: function (response) {
                        that.editor.setHTML(response.message);
                        if(callback) callback();
                    },
                    error: function(e) {
                        console.log("Something went wrong!")
                    }
                });
                return this;
            }
        }
    };

    function Pagination(el, contract) {
        return {
            show: function() {
                $(el).twbsPagination({
                    totalPages: contract.totalPages,
                    visiblePages: 5,
                    startPage: contract.currentPage,
                    onPageClick: function (event, page) {
                        contract.loadPageText(page);
                        contract.loadPageAnnotations(page);
                    }
                });
                return this;
            }
        };
    };    

    function ContractAnnotator(el, contractId, api) {
        return {
            init: function() {
                var options = {readOnly: true};
                this.content = $(el).annotator(options);
                this.content.annotator('addPlugin', 'Tags');
                this.availableTags = {!! json_encode(trans("codelist/annotationTag.annotation_tags")) !!};
                return this;
            },
            load: function(page) {
                if(this.content.data('annotator').plugins.Store) {
                    var store = this.content.data('annotator').plugins.Store;
                    if(store.annotations) store.annotations = [];
                    store.options.loadFromSearch = {'url': api,'contract': contractId,'document_page_no': page};
                    store.loadAnnotationsFromSearch(store.options.loadFromSearch)                
                } else {
                    this.content.annotator('addPlugin', 'Store', {
                        // The endpoint of the store on your server.
                        prefix: '/api',
                        // Attach the uri of the current page to all annotations to allow search.
                        loadFromSearch: {
                            'url': api,
                            'contract': contractId,
                            'document_page_no': page
                        }
                    });                    
                }

            },            
        };
    };

    jQuery(function ($) {
        var contract1 = new Contract({
            position: "left",
            id: '{{$contract1["metadata"]->id}}',
            totalPages:'{{$contract1["metadata"]->pages->count()}}',
            currentPage: 1,
            textLoadAPI: "{{route('contract.page.get', ['id'=>$contract1['metadata']->id])}}",
            annotationAPI: "{{route('contract.page.get', ['id'=>$contract1['metadata']->id])}}", 
        });

        var contract2 = new Contract({
            position: "right",
            id: '{{$contract2["metadata"]->id}}',
            totalPages:'{{$contract2["metadata"]->pages->count()}}',
            currentPage: 1,
            textLoadAPI: "{{route('contract.page.get', ['id'=>$contract2['metadata']->id])}}",
            annotationAPI: "{{route('contract.page.get', ['id'=>$contract2['metadata']->id])}}"

        });        
        contract1.init().loadPageText(1).loadPagination().loadPageAnnotations(1);
        contract2.init().loadPageText(1).loadPagination().loadPageAnnotations(1);
    });

    </script>
@stop
