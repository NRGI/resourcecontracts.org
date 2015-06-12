@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> Annotate {{$contract->metadata->project_title}}</div>

        <div class="view-wrapper">
            <div id="pagelist"></div>
            <div class="document-wrap">
                <div class="left-document-wrap annotate">
                    <div class="quill-wrapper">
                        <!-- Create the toolbar container -->
                        <div id="toolbar" class="ql-toolbar ql-snow">
                        </div>
                        <div id="editor" class="editor ql-container ql-snow">
                            <div class="ql-editor" id="ql-editor-1" contenteditable="true">

                            </div>
                            <div class="ql-paste-manager" contenteditable="true"></div>
                        </div>
                    </div>
                </div>
                <div class="right-document-wrap">
                    <canvas id="the-canvas"></canvas>
                </div>
            </div>
        </div>

    </div>
@stop

@section('script')
    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script>
        jQuery(function ($) {
            //if loaded for the first time, load page 1
            var page = '{{$page}}';
            pageLoader(fileFoler, page);
            var contractId = "{{$contract->id}}"
            var url = '{{route('contract.page.get', ['id'=>$contract->id])}}';
            var content = $('.annotate').annotator();
            var setupAnnotator = function (area, documentId) {
                area.annotator('addPlugin', 'Store', {
                    // The endpoint of the store on your server.
                    prefix: '/api',
                    // Attach the uri of the current page to all annotations to allow search.
                    annotationData: {
                        'url': url,
                        'contract': contractId,
                        'document_page_no': documentId
                    },
                    loadFromSearch: {
                        'contract': contractId,
                        'document_page_no': documentId,
                        'url': url
                    }
                });
                var availableTags = {!! json_encode(config('nrgi.annotation_tags')) !!};

                content.annotator('addPlugin', 'Tags');
                $('.annotate').data('annotator').plugins.Tags.input.autocomplete({
                    source: availableTags,
                    multiselect: true
                });
            };
            setupAnnotator(content, page);

        });
        //defining format to use .format function
        String.prototype.format = function () {
            var formatted = this;
            for (var i = 0; i < arguments.length; i++) {
                var regexp = new RegExp('\\{' + i + '\\}', 'gi');
                formatted = formatted.replace(regexp, arguments[i]);
            }
            return formatted;
        };

        var totalPages = '{{count($pages)}}';
        var fileFoler = '{{ $contract->id }}';
        var page = '{{$page}}';

        //fill the page numbers
        var divClass = "";
        for (var index = 1; index <= totalPages; ++index) {
            if (index == page) {
                divClass = "active";
            } else {
                divClass = "";
            }
            $('#pagelist').append('<a class="{1}" href="{{route('contract.annotations.index', ['id'=>$contract->id])}}?page={0}">{0}</a>&nbsp;'.format(index, divClass));
        }

        //read the url content
        function loadPageText(page) {
            $.ajax({
                url: '{{route('contract.page.get', ['id'=>$contract->id])}}',
                data: {'page': page},
                type: 'GET',
                dataType: 'JSON',
                success: function (response) {
                    editor.setHTML(response.message);
                }
            });
        }

        var editor = new Quill('#editor', {theme: 'snow', readOnly: true});
        function pageLoader(fileFoler, page) {
            //create text and pdf location based on the defined structure
            var pdfLocation = "/data/{0}/pages/{1}.pdf".format(fileFoler, page);
            loadPageText(page);

            PDFJS.workerSrc = '/js/lib/pdfjs/pdf.worker.js';

            PDFJS.getDocument(pdfLocation).then(function (pdf) {
                // Using promise to fetch the page
                pdf.getPage(1).then(function (page) {
                    var scale = 1;
                    var viewport = page.getViewport(scale);
                    // Prepare canvas using PDF page dimensions
                    var canvas = document.getElementById('the-canvas');
                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                });
            });
        }
    </script>
@stop
