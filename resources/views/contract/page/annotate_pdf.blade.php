@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">

    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
@stop


@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="wordwrap pull-left"> @lang('contract.editing')
                <span>{{str_limit($contract->title, 100)}}</span>

            <a href="{{route('contract.show', $contract->id)}}" class="go-back">Go back to
                        Contract detail</a>
            </div>
            <div class="pull-right">
                <a class="btn btn-default btn-annotation pull-right" href='#'>Annotations</a>
                <div id="annotations-list-view" class="annotation-list-view" style="display:none"></div>
                <a class="btn btn-default btn-metadata pull-right" href="#">Metadata</a>
                <div id="metadata" class="metadata" style="display:none"></div>
            </div>
        </div>
        <div class="view-wrapper" style="background: #F6F6F6">
             <div id="message" style="padding: 0px 16px"></div>
            <div id="pagination"></div>
            <div id="search-form">
                <form method="POST" action="{{route('contract.page.search', ["id"=>$contract['contract_id']])}}" accept-charset="UTF-8" class="form-inline page-search pull-right">
                    <div class="form-group">
                        <div class="input-group">
                            <input id="textfield" class="form-control" placeholder="Search..." name="q" type="text">
                        </div>
                    </div>
                    <input class="btn btn-primary" type="submit" value="Search">
                    <a href='#' id="search-results-cache" style="display:none;" class="pull-right">Results</a>
                </form>
            </div>
            <div class="document-wrap">
            <div class="left-document-wrap" id="annotatorjs">
                    <div class="quill-wrapper">
                        <!-- Create the toolbar container -->
                        <div id="toolbar" class="ql-toolbar ql-snow">
                            <button class="ql-bold">B</button>
                            <button class="ql-italic">I</button>
                        </div>
                        <div id="editor" style="height: 750px" class="editor ql-container ql-snow">
                        </div>
                        <button name="submit" value="submit" id="saveButton" class="btn">Save</button>
                    </div>
                </div>
                <div class="right-document-wrap search" id="annotate-pdf">
                    <canvas class="annotate" id="pdfcanvas"></canvas>
                    <div id="search-results-list" style='display:none'></div>
                    <!-- <div id="annotations_list" class="annotation-list" style="display:none"></div>                     -->
                </div>
            </div>
        </div>

    </div>
@stop

@section('script')
    <script src='http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js'></script>
    <script src='http://assets.annotateit.org/annotator/v1.2.5/annotator-full.min.js'></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>

    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.simplePagination.js') }}"></script>
    <script src="{{ asset('js/lib/underscore.js') }}"></script>
    <script src="{{ asset('js/lib/backbone.js') }}"></script>

    <script src="{{ asset('js/custom/rc.utils.js') }}"></script>
    <script src="{{ asset('js/custom/rc.contract.js') }}"></script>
    <script src="{{ asset('js/custom/rc.page.js') }}"></script>
    <script src="{{ asset('js/custom/rc.pagination.js') }}"></script>
    <script src="{{ asset('js/custom/rc.pdf.js') }}"></script>
    <script src="{{ asset('js/custom/rc.texteditor.js') }}"></script>
    <script src="{{ asset('js/custom/annotator.plugin.event.js') }}"></script>
    <script src="{{ asset('js/custom/annotator.plugin.categories.js') }}"></script>
    <script src="{{ asset('js/custom/annotator.plugin.tags.js') }}"></script>
    <script src="{{ asset('js/custom/annotorious.okfn.0.3.js') }}"></script>
    <script src="{{ asset('js/custom/rc.annotations.js') }}"></script>
    <script src="{{ asset('js/custom/rc.search.js') }}"></script>
    <script src="{{ asset('js/custom/rc.annotator.js') }}"></script>
    <script src="{{ asset('js/custom/rc.metadata.js') }}"></script>
    <script src="{{ asset('js/custom/rc.scroll.js') }}"></script>

    <script>
    var contractEvents = {};
    _.extend(contractEvents, Backbone.Events);
    var contractMetadata = {!!json_encode($contract['metadata'])!!};
    var currentPage = '{{$page->page_no}}';
    var contract = new Contract({
        id: '{{$contract->id}}',
        metadata: contractMetadata,
        totalPages: '{{$contract->pages->count()}}',
        currentPage: '{{$page->page_no}}',
        currentPageId: '{{$page->id}}',

        canEdit: {{$canEdit}},
        canAnnotate: true,
        // annotatorjsAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}",
        // annotatationPullAPI: "{{route('contract.annotations', ['id'=>$contract->id])}}"
    });

    var pageModel = new Page({
        pageNumber: currentPage || 1,
        loadUrl: "{{route('contract.page.get', ['id'=>$contract->id])}}",
        saveUrl: "{{route('contract.page.store', ['id'=>$contract->id])}}",
        contractModel: contract,
        eventsPipe: contractEvents
    }).load(currentPage);

    var paginationView = new PaginationView({
        el: "#pagination",
        totalPages: contract.getTotalPages(),
        model: pageModel,
        eventsPipe: contractEvents
    }).render();

    //text editor module
    var textEditorView = new TextEditorView({
        el: "#editor",
        model: pageModel
    }).render();

    //pdf view module
    var pdfView = new PdfView({
        el: "pdfcanvas",
        model: pageModel
    });

    var scroller = new Scroller({
        editorEl: "#editor",
        eventsPipe: contractEvents
    });

    //search module
    var searchResultCollection = new SearchResultCollection({
        eventsPipe: contractEvents
    });
    var searchFormView = new SearchFormView({
        el: '#search-form',
        collection: searchResultCollection,
        url: "{{route('contract.page.search', ["id"=>$contract->id])}}",
        eventsPipe: contractEvents
    }).render();
    var searchResultsList = new SearchResultListView({
        el: '#search-results-list',
        collection: searchResultCollection,
        searchOverlayLayer: '#pdfcanvas',
        eventsPipe: contractEvents
    });

    // $('#saveButton').click(function (el) {
    //     pageView.saveClicked();
    // });

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
<script type="text/javascript">
//metadata view module
var metadataView = new MetadataView({
    el: "#metadata",
    metadata: contractMetadata
}).render();
var metadataButtonView = new MetadataButtonView({
    el: '.btn-metadata',
    metadataView: metadataView
});
</script>

<script type="text/template" id="annotation-list-title-template">
    <div>
        <span id='total-count'></span>
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
        <a href='#' class="quote"><%= quote %> [P <%= page %>]</a>
        <span class="text"><%= text %></span>
        <% _.each(tags, function(tag) { %>
            <span class="tag"><%= tag %></span>
        <% }); %>
    </div>
</script>

<script type="text/javascript">
    var categories = {!! json_encode(trans("codelist/annotation.categories")) !!}
    var annotationCategories = new AnnotationCategories();
    _.each(categories, function(category) {
        annotationCategories.add({name: category});
    });
    var annotationCollection = new AnnotationCollection();
    annotationCollection.url = "{{route('contract.annotations', ['id'=>$contract->id])}}";
    annotationCollection.fetch({reset: true});
    var annotatorjsView = new AnnotatorjsView({
        el: "#annotate-pdf",
        model: pageModel,
        contractModel: contract,
        api: "{{route('contract.annotations', ['id'=>$contract->id])}}",
        availableTags: {!! json_encode(trans("codelist/annotation.tags")) !!},
        collection: annotationCollection,
        annotationCategories: annotationCategories
    }).render();
    var annotationsTitleView = new AnnotationsTitleView({
        collection: annotationCollection,
        annotationCategories: annotationCategories
    });
    var annotationsListView = new AnnotationsListView({
        el: "#annotations-list-view",
        collection: annotationCollection,
        annotationCategories: annotationCategories,
        eventsPipe: contractEvents,
        annotationsTitleView: annotationsTitleView.render()
    }).render();

    var annotationsButtonView = new AnnotationsButtonView({
        el: '.btn-annotation',
        annotationsListView: annotationsListView
    });
</script>
@stop
