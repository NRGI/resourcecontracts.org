@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/simplePagination.css') }}">
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
            <div class="left-document-wrap" id="annotate_left">
                    <div class="quill-wrapper">
                        <div id="pagelist_left"></div>
                        <div id="editor_left" class="editor">
                        </div>
                    </div>
                    <div id="annotations_left" class="annotation-list">
                    </div>                    
                </div>
                <div class="right-document-wrap" id="annotate_right">
                    <div class="quill-wrapper">
                        <div id="pagelist_right"></div>
                        <div id="editor_right" class="editor">
                        </div>
                    </div>
                    <div id="annotations_right" class="annotation-list">
                    </div>                    
                </div>
            </div>
        </div>

    </div>
@stop

@section('script')
<script>
var contract1Annotations = [];
</script>
@forelse($contract1['annotations'] as $annotation)
    <script>var tags = [];</script>
   @foreach($annotation['tags'] as $tag)
    <script>tags.push('{{$tag}}');</script>            
    @endforeach
    <script>
    contract1Annotations.push({
        'page': '{{$annotation["page"]}}',
        'text': '{{$annotation["text"]}}',
        'quote': '{{$annotation["quote"]}}',
        'tags': tags
    });
    </script>
@empty 
@endforelse

<script>
var contract2Annotations = [];
</script>
@forelse($contract2['annotations'] as $annotation)
    <script>var tags = [];</script>
   @foreach($annotation['tags'] as $tag)
    <script>tags.push('{{$tag}}');</script>            
    @endforeach
    <script>
    contract2Annotations.push({
        'page': '{{$annotation["page"]}}',
        'text': '{{$annotation["text"]}}',
        'quote': '{{$annotation["quote"]}}',
        'tags': tags
    });
    </script>
@empty 
@endforelse

    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.twbsPagination.js') }}"></script>
    <script src="{{ asset('js/jquery.simplePagination.js') }}"></script>
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
                this.annotator = new ContractAnnotator("#annotate_{0}".format(options.position), this.id, options.annotationAPI).init();
                return this;
            },
            loadPageText: function(page) {
                this.currentPage = page;
                this.editor.load(options.textLoadAPI, this.currentPage);
                return this;
            },
            loadPagination: function() {
                this.pagination = new Pagination("#pagelist_{0}".format(options.position), this).show();
                return this;
            },
            loadPageAnnotations: function(page) {
                this.annotator.load(page);
                return this;
            },
            listAllAnnotations: function() {
                new AnnotationsList("#annotations_{0}".format(options.position), this, options.annotations).init();
                return this;
            },
            loadPage: function(page) {
                this.pagination.setPage(page);
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
                this.pagination = $(el).pagination({
                    pages: contract.totalPages,
                    displayedPages: 5,
                    cssStyle: 'light-theme',
                    onPageClick: function (page, event) {
                        contract.loadPageText(page);
                        contract.loadPageAnnotations(page);
                    }
                });
                return this;
            // this.pagination = $(el).twbsPagination({
            //         totalPages: contract.totalPages,
            //         visiblePages: 5,
            //         startPage: contract.currentPage,
            //         onPageClick: function (event, page) {
            //             contract.loadPageText(page);
            //             contract.loadPageAnnotations(page);
            //         }
            //     });
            //     return this;
            },
            setPage: function(page) {
                // console.log(this.pagination);
                // this.pagination.pagination('selectPage', page);
                this.pagination.pagination('drawPage', page)
                return this;
                // this.pagination.show(page);
                // return this;
            }
        };
    };    

    function AnnotationsList(el, contract, annotations) {
        return {
            init: function() {
                $(el).append("<hr><ul>");
                annotations.forEach(function(annotation) {
                    $(el).append("<li><span><a onclick='annotationClicked(this,"+contract.id+","+annotation.page+")' href='#'>{0}</a> [Page {1}]</span><br><p>{2}</p></li>".format(annotation.quote, annotation.page, annotation.text));
                });
                $(el).append("</ul>");
            },
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

    var contract1 = new Contract({
        position: "left",
        id: '{{$contract1["metadata"]->id}}',
        totalPages:'{{$contract1["metadata"]->pages->count()}}',
        currentPage: 1,
        textLoadAPI: "{{route('contract.page.get', ['id'=>$contract1['metadata']->id])}}",
        annotationAPI: "{{route('contract.page.get', ['id'=>$contract1['metadata']->id])}}",
        annotations: contract1Annotations
    });

    var contract2 = new Contract({
        position: "right",
        id: '{{$contract2["metadata"]->id}}',
        totalPages:'{{$contract2["metadata"]->pages->count()}}',
        currentPage: 1,
        textLoadAPI: "{{route('contract.page.get', ['id'=>$contract2['metadata']->id])}}",
        annotationAPI: "{{route('contract.page.get', ['id'=>$contract2['metadata']->id])}}",
        annotations: contract2Annotations
    }); 
    function getContract(id) {
        if(id == contract1.id) return contract1;
        else if(id == contract2.id) return contract2;
        return null;
    }

    function annotationClicked(elem, contractId, page) {
        if(getContract(contractId)) {
            getContract(contractId).loadPage(page);
            // getContract(contractId).loadPageText(page);
            // getContract(contractId).loadPageAnnotations(page);
        }
    }

    jQuery(function ($) {
        contract1.init().loadPageText(1).loadPagination().loadPageAnnotations(1);
        contract1.listAllAnnotations();
        contract2.init().loadPageText(1).loadPagination().loadPageAnnotations(1);
        contract2.listAllAnnotations();
    });

    </script>
@stop
