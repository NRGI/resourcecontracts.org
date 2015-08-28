@extends('layout.app-full')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contract-viewer.css') }}">
@stop


@section('content')
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-fixed">
            <div class="word-wrapper">
                <div class="wordwrap pull-left"> 
                    <a href="{{route('contract.show', $contract->id)}}" class="back pull-left">Go back</a>
                    <span class="pull-left">{{str_limit($contract->title, 80)}}</span>
                </div>
                <div class="pull-right">
                    <div class="view-metadata">
                        <a class="btn-metadata pull-right" href="#">View Metadata</a>
                    </div>
                    <div id="metadata" class="metadata" style="display:none"></div>
                </div>
            </div>
            <div class="document-wrap-head">
            <div class="navigation">
                <a href='#' id="pdf-text-view">Text Pdf View</a>
                <div class="column-text column-common">
                    <div id="search-form">
                        <form method="POST" action="{{route('contract.page.search', ["id"=>$contract['contract_id']])}}" accept-charset="UTF-8" class="form-inline page-search pull-right">
                            <div class="form-group">
                                <div class="input-group">
                                    <input id="textfield" class="form-control" placeholder="Search in this document.." name="q" type="text">
                                </div>
                            </div>
                            <input class="btn btn-primary pull-left" type="submit" value="Search">
                            <a href='#' id="search-results-cache" style="display:none;" class="pull-right">Results</a>
                        </form>
                    </div>
                </div>
                <div class="column-text column-common">
                    <div id="pagination" class="view-text-pagination pull-right">
                        <a href="#" class="previous">&laquo;</a>
                        <input id="goto_page" placeholder="Goto Page" type="number" class="small-input" title="Go to page">
                        <a href="#" class="next">&raquo;</a>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="view-wrapper">
             <div id="message" style="padding: 0px 16px"></div>
            <div class="document-wrap">
                <div class="column-text">
                    <span class="text-view-block" >

                        <div id="text-viewer-wrapper-overflow-scroll" class="_ annotator-text view-wrap">
                            <!-- 
                            don't understand why class="annotator-text" doesn't work, putting any dummy class makes the popup appear 
                            similarly one extra <div> is required after <div class="annotator-text">, otherwise the annotation also doesn't appear
                            -->
                            <div></div>
                            <div class="text-viewer-wrapper">
                                <div id="text-viewer-overflow-scroll" class="text-viewer">Loading ...</div>    
                                <button id="saveButton" value="Save">Save</button>
                            </div>
                        </div>
                    </span>                            
                </div>
                <div class="column-pdf">
                    <span class="pdf-view-block">
                        <div class="annotator-pdf view-wrap">
                            <canvas id="pdfcanvas" width="500px" height="700px"></canvas>
                        </div>
                    </span>
                </div>
                <div class="column-search-results" style='display:none'>
                    <div id="search-results-list"></div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('script')
    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>

    <script src="{{ asset('js/lib/underscore.js') }}"></script>    
    <script src="{{ asset('js/lib/backbone.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.xpath.js') }}"></script> 
    <script src="{{ asset('js/lib/jquery.waypoints.js') }}"></script>    

    <script src="{{ asset('js/custom/rc.pages.collection.js') }}"></script> 
    <script src="{{ asset('js/custom/rc.text.editor.js') }}"></script>

    <script src="{{ asset('js/custom/rc.utils.js') }}"></script>
    <script src="{{ asset('js/custom/rc.contract.js') }}"></script>

    <script src="{{ asset('js/custom/rc.pdf.js') }}"></script>

    <script src="{{ asset('js/custom/rc.metadata.js') }}"></script>
    <script src="{{ asset('js/custom/rc.search.js') }}"></script>    

    <script type="text/template" id="text-page-partial-view">
        <span id="<%= page_no %>">
        <span><%= page_no %></span>
        <%= nl2br(text) %>
        </span>
    </script>
    <script type="text/template" id="metadata-view-template">
        <div class="popup-metadata">
        <p><strong>Contract Title:</strong><%= contract_name %></p>
        <p><strong>Country:</strong> <%= country.name %></p>
        <p><strong>Date of signature:</strong> <%= signature_date %></p>
        <p><strong>Resource:</strong>
            <%=resource%>
        </p></div>
    </script>    
    <script>
    var contract = new Contract({
        id: '{{$contract->id}}',
        metadata: {!!json_encode($contract['metadata'])!!},
        canEdit: {{$canEdit}},
        totalPages: '{{$contract->pages->count()}}',        
        canAnnotate: false
    });
    var rcEvents = {};
    _.extend(rcEvents, Backbone.Events);

    var Tabs = Backbone.View.extend({
        el: '.panel',
        events: {
            "click #text-view": "textView",
            "click #pdf-view": "pdfView",
            "click #pdf-text-view": "textPdfView",
            "click input[type=submit]": "searchView",
            "click a#search-results-cache": "searchView"
        },
        initialize: function() {
            this.textTabEl = $('.text-view-block');           
            this.pdfTabEl = $('.pdf-view-block');
            this.currentPage = null;
            this.viewerPage = null;
            this.editorPage = null;
            this.currentPdfPage = null;
            this.initTextView();
            this.initPdfView();
            this.initSearch();
            this.textPdfView();
        },
        textView: function(e) {
            if(e) e.preventDefault();
            $('#text-view').addClass('active');
            $('#pdf-view').removeClass('active');
            $('#pdf-text-view').removeClass('active');

            $('.column-text').show();
            $('.column-text').removeClass('column-common');
            $('.column-pdf').hide();
            $('.column-search-results').hide();  
        },
        pdfView: function(e) {
            if(e) e.preventDefault();
            $('#text-view').removeClass('active');
            $('#pdf-view').addClass('active');
            $('#pdf-text-view').removeClass('active');
            $('.column-text').hide();
            $('.column-pdf').show();
            $('.column-pdf').removeClass('column-common');
            $('.column-annotations').show();
            $('.column-search-results').hide();  
        },
        textPdfView: function(e) {
            if(e) e.preventDefault();
            $('#text-view').removeClass('active');
            $('#pdf-view').removeClass('active');
            $('#pdf-text-view').addClass('active');
            $('.column-text').show();
            $('.column-text').addClass('column-common');
            $('.column-pdf').show();
            $('.column-pdf').addClass('column-common');
            $('.column-search-results').hide();  
        },
        searchView: function(e) {
            if(e) e.preventDefault();
            $('.column-text').show();
            $('.column-text').removeClass('column-common');
            $('.column-pdf').hide();
            $('.column-search-results').show();               
        },
        initTextView: function() {
            // this.viewerPages = new ViewerPagesCollection([], {
            //     url: "{{route('contract.allpage.get', ['id'=>$contract->id])}}"
            // });
            // this.viewerPages.fetch({reset: true});
            // var currentPage = new ViewerCurrentPage({
            //     collection: this.viewerPages
            // });
            var currentPage = 1;
            this.editorPage = new EditorCurrentPage({
                pageNumber: currentPage || 1,
                loadUrl: "{{route('contract.page.get', ['id'=>$contract->id])}}",
                saveUrl: "{{route('contract.page.store', ['id'=>$contract->id])}}",
                eventsPipe: rcEvents,
                totalPages: contract.getTotalPages()
            }).fetch(currentPage);

            new TextEditorView({
                el: '#text-viewer-wrapper-overflow-scroll',
                rcEvents: rcEvents,
                model: this.editorPage,
            });
            new TextEditorPagination({
                el: '#pagination',
                currentPage: this.editorPage,
                rcEvents: rcEvents,
            });  
        },
        initPdfView: function() {
            //pdf view module
            var pdfView = new PdfView({
                el: "pdfcanvas",
                currentPage: this.editorPage
            });
        },
        initSearch: function() {
            var searchResultCollection = new SearchResultCollection({
                eventsPipe: rcEvents
            }); 
            this.listenTo(searchResultCollection, 'dataCollected', this.searchView);
            this.bind('dataCollected', this.searchView, this);                               
            var searchFormView = new SearchFormView({
                el: '#search-form',
                collection: searchResultCollection,
                url: "{{route('contract.page.search', ["id"=>$contract->id])}}",
                eventsPipe: rcEvents
            }).render();
            new SearchResultListView({
                el: '#search-results-list',
                collection: searchResultCollection,
                // searchOverlayLayer: '#pdfcanvas',
                eventsPipe: rcEvents
            });
        }
    });
    new Tabs();

//metadata view module
var contractMetadata = {!!json_encode($contract['metadata'])!!};
var metadataView = new MetadataView({
    el: "#metadata",
    metadata: contractMetadata
}).render();
var metadataButtonView = new MetadataButtonView({
    el: '.btn-metadata',
    metadataView: metadataView
});
</script>

@stop
