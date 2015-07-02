@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <style>
    .highlight {
        background-color: red;
    }
        .popup-metadata {
            position: absolute;
            background-color: #FFFFFF;
            padding: 20px;
            right: 0px;
            z-index: 100;
            border: 1px solid #ccc;
            top: 44px;
            font-size: 14px;
            width: 330px;
        }
    </style>
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> @lang('contract.editing') <span>{{$contract->metadata->contract_name or $contract->metadata->project_title}}</span>   <a class="btn btn-default pull-right" href="{{route('contract.show', $contract->id)}}">Back</a>
            <a class="btn btn-default btn-annotation pull-right" href="{{route('contract.annotations.list', $contract->id)}}">Annotations</a>
            <a class="btn btn-default btn-metadata btn-annotation pull-right" href="{{route('contract.metadata', $contract->id)}}">Metadata</a>
        </div>

        <div class="view-wrapper" style="background: #F6F6F6">
             <div id="message" style="padding: 0px 16px"></div>
            <div id="pagination"></div>
            <div id="searchForm"></div>
            <script type="text/template" id="searchFormTemplate">
            {!! Form::open(['route' => ['contract.page.search', $contract->id], 'method' => 'POST', 'class'=>'form-inline page-search pull-right', 'style' => 'width: 421px; margin: 0 auto 23px;']) !!}
            <div class="form-group">
                <div class="input-group">
                    {!! Form::text('q', null, ['id'=> 'textfield', 'class' => 'form-control', 'placeholder' => 'Search...' , 'style' => 'padding:15px; width:280px']) !!}
                </div>
            </div>
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
            </script>

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
                <div class="right-document-wrap search">
                    <canvas id="pdfcanvas"></canvas>
                    <div id="SearchResultsList" style='display:none'></div>
                </div>
                <div class="searchresults"></div>
            </div>
        </div>

    </div>
@stop

@section('script')
    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>
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
        currentPage: '{{$page->page_no}}',
        currentPageId: '{{$page->id}}',

        editorEl: '#editor',
        paginationEl: '#pagination',
        // annotationEl: '#annotation',
        pdfviewEl: 'pdfcanvas',
        annotatorjsEl: '#annotatorjs',

        canEdit: {{$canEdit}},
        canAnnotate: {{$canAnnotate}},

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
        pdfView: new PdfView({
            pdfviewEl: contract.getPdfviewEl(),
            pageModel: contract.getPageModel()
        }),
        annotatorjsView: new AnnotatorjsView({
            annotatorjsEl: contract.getAnnotatorjsEl(),
            pageModel: contract.getPageModel(),
            contractModel: contract,
            tags:{!! json_encode(trans("codelist/annotationTag.annotation_tags")) !!}
        }),
        searchFormView: new SearchFormView({
            collection: contract.searchResultCollection,
            el: '#searchForm'
        }),
        searchResultsList: new SearchResultListView({
            el: '#SearchResultsList',
            collection: contract.searchResultCollection,
            pageModel: contract.getPageModel(),
        }),
    }).render();

    $('#saveButton').click(function (el) {
        pageView.saveClicked();
    });

    $('.btn-metadata').click(function (el) {
        el.preventDefault();
        var url = $(this).attr('href');
        pageView.showMetadata(url);
    });

    </script>
@stop
