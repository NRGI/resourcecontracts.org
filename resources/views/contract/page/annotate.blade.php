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
                <a href="#" id="text-view">text</a> 
                <a href='#' id="pdf-view">pdf</a> 
                <a href='#' id="pdf-text-view">both</a>
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
                <div class="column-pdf column-common" style="display:none">
                    <div id="pdf-pagination" class="view-text-pagination pull-right">
                        <a href="#" class="previous">&laquo;</a>
                        <input id="goto_pdfpage" placeholder="Goto Page" type="number" class="small-input" title="Go to page">
                        <a href="#" class="next">&raquo;</a>
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
                                <div id="text-viewer-overflow-scroll" class="text-viewer" >Loading ...</div>    
                            </div>
                            
                        </div>
                    </span>                            
                </div>
                <div class="column-pdf" style="display:none">
                    <span class="pdf-view-block">
                        <div class="annotator-pdf view-wrap">
                            <canvas id="pdfcanvas" width="500px" height="700px"></canvas>
                        </div>
                    </span>
                </div>
                <div class="column-annotations">
                    <div id="annotations-list-view" class="annotation-list-view annotations-view-block"></div>                    
                </div>
                <div class="column-search-results" style='display:none'>
                    <div id="search-results-list"></div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('script')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>

    <script src="{{ asset('js/lib/underscore.js') }}"></script>    
    <script src="{{ asset('js/lib/backbone.js') }}"></script>
    <script src="{{ asset('js/lib/jquery.xpath.js') }}"></script> 
    <script src="{{ asset('js/lib/jquery.waypoints.js') }}"></script>    

    <script src="{{ asset('js/annotator.plugin.annotorious.js') }}"></script>
    <script src="{{ asset('js/custom/rc.pages.collection.js') }}"></script> 
    <script src="{{ asset('js/custom/rc.text.viewer.js') }}"></script> 

    <script src="{{ asset('js/custom/rc.utils.js') }}"></script>
    <script src="{{ asset('js/custom/rc.contract.js') }}"></script>

    <script src="{{ asset('js/custom/rc.pdf.js') }}"></script>

    <script src="{{ asset('js/custom/annotator.plugin.event.js') }}"></script>
    <script src="{{ asset('js/custom/annotator.plugin.categories.js') }}"></script>
    <script src="{{ asset('js/custom/annotator.plugin.tags.js') }}"></script>
    <script src="{{ asset('js/custom/rc.annotations.js') }}"></script>
    <script src="{{ asset('js/custom/rc.annotator.js') }}"></script>
    <script src="{{ asset('js/custom/rc.metadata.js') }}"></script>
    <script src="{{ asset('js/custom/rc.search.js') }}"></script>    

    <script type="text/template" id="text-page-partial-view">
        <span id="<%= page_no %>">
        <span><%= page_no %></span>
        <%= nl2br(text) %>
        </span>
    </script>
    <script type="text/template" id="annotation-list-title-template">
        <div class="annotation-title">Annotations</div>
        <div>            
            <button id='all'>All - <%= total %></button>
            <button id='done-annotated'>Done - <%= done %></button>
            <button id='not-annotated'>Remainings - <%= remaining %></button>
        </div>
    </script>
    <script type="text/template" id="annotation-category-no-items-template">
        <div class="annotation-category-not-done">
            <%= categoryName %> <small>Not annotated yet</small>
        </div>
    </script>
    <script type="text/template" id="annotation-category-with-items-template">
        <div class="annotation-category-done">
            <%= categoryName %> [<%= categoryItemsCount %>]
            <ul id="<%= elemId %>"></ul>
        </div>
    </script>
    <script type="text/template" id="annotation-item-view-template">
        <div>
            <span class="text"><%= text %></span>

            <a href='#' class="quote">
                <% if (typeof quote !== "undefined") { %>
                <%= quote %>
                <% } %>
            </a>

            <span class="page-no">Pg <a href='#'><%= page %></a></span>

            <% if (typeof tags !== "undefined") { %>
            <% _.each(tags, function(tag) { %>
                <span class="tag"><%= tag %></span>
            <% }); %>
            <% } %>
        </div>
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
        canAnnotate: true || {{$canAnnotate}},
    });
    var annotationCollection = new AnnotationCollection();
    var annotationCategories = new AnnotationCategories();
    var rcEvents = {};
    _.extend(rcEvents, Backbone.Events);

    var categories = {!! json_encode(trans("codelist/annotation.categories")) !!}
    var annotationCategories = new AnnotationCategories();
    _.each(categories, function(category) {
        annotationCategories.add({name: category});
    });
    var annotationCollection = new AnnotationCollection();
    annotationCollection.url = "{{route('contract.annotations', ['id'=>$contract->id])}}";
    annotationCollection.fetch({reset: true});

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
            this.currentPdfPage = null;
            this.pdfAnnotatorjsView = null;
            this.annotationsTabEl = $('.annotations-view-block');                        
            this.initAnnotationsView();
            this.initTextView();
            this.initPdfView();
            this.initSearch();
            this.textView();
        },
        textView: function(e) {
            if(e) e.preventDefault();
            $('#text-view').addClass('active');
            $('#pdf-view').removeClass('active');
            $('#pdf-text-view').removeClass('active');

            $('.column-text').show();
            $('.column-text').removeClass('column-common');
            $('.column-pdf').hide();
            $('.column-annotations').show();
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
            if(!this.pdfAnnotatorjsView) {
                this.pdfAnnotatorjsView = new PdfAnnotatorjsView({
                    el: ".annotator-pdf",
                    currentPage: this.currentPdfPage,
                    contract: contract,
                    api: "{{route('contract.annotations', ['id'=>$contract->id])}}",
                    availableTags: {!! json_encode(trans("codelist/annotation.tags")) !!},
                    collection: annotationCollection,
                    annotationCategories: annotationCategories,
                    enablePdfAnnotation: true
                });   
            }
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
            $('.column-annotations').hide();
            $('.column-search-results').hide();  
        },
        searchView: function(e) {
            if(e) e.preventDefault();
            $('.column-text').show();
            $('.column-text').removeClass('column-common');
            $('.column-pdf').hide();
            $('.column-annotations').hide();
            $('.column-search-results').show();               
        },
        initTextView: function() {
            this.viewerPages = new ViewerPagesCollection([], {
                url: "{{route('contract.allpage.get', ['id'=>$contract->id])}}"
            });
            this.viewerPages.fetch({reset: true});
            var currentPage = new ViewerCurrentPage({
                collection: this.viewerPages
            });
            new TextViewerView({
                el: '#text-viewer-wrapper-overflow-scroll',
                collection: this.viewerPages,
                rcEvents: rcEvents,
                currentPage: currentPage
            });
            new TextViewerPagination({
                el: '#pagination',
                collection: this.viewerPages,
                currentPage: currentPage
            });  
            new AnnotatorjsView({
                el: ".annotator-text",
                currentPage: currentPage,
                contract: contract,
                api: "{{route('contract.annotations', ['id'=>$contract->id])}}",
                availableTags: {!! json_encode(trans("codelist/annotation.tags")) !!},
                collection: annotationCollection,
                annotationCategories: annotationCategories,
                enablePdfAnnotation: false
            }).render();
        },
        initPdfView: function() {
            var pdfPages = new PdfPagesCollection();
            pdfPages.url = "{{route('contract.allpage.get', ['id'=>$contract->id])}}"
            pdfPages.fetch({reset: true});
            this.currentPdfPage = new PdfCurrentPage({
                collection: pdfPages
            });
            //pdf view module
            var pdfView = new PdfView({
                el: "pdfcanvas",
                collection: pdfPages,
                currentPage: this.currentPdfPage
            });
            pdfPages.on('reset', function() {
                pdfView.render();
            }, this);
            new PdfViewerPagination({
                el: '#pdf-pagination',
                collection: pdfPages,
                currentPage: this.currentPdfPage
            });
        },
        initAnnotationsView: function() {
            var annotationsTitleView = new AnnotationsTitleView({
                collection: annotationCollection,
                annotationCategories: annotationCategories
            });
            var annotationsListView = new AnnotationsListView({
                el: "#annotations-list-view",
                collection: annotationCollection,
                annotationCategories: annotationCategories,
                eventsPipe: rcEvents,
                annotationsTitleView: annotationsTitleView.render()
            }).render();
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
