@extends('layout.app-full')

@section('content')
    <script src="{{ URL::asset('js/lib/quill/quill.js') }}"></script>
    <script src="{{ URL::asset('js/lib/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ URL::asset('js/lib/pdfjs/pdf.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}"/>


    <div class="document-view-wrapper">
        <h1 class="edit-title">
            Annotate
            <div class="title">{{$contract->metadata->project_title}}</div>
        </h1>
        <div class="view-wrapper">
            <div id="pagelist"></div>
            <div class="document-wrap">
                <div class="left-document-wrap annotate">
                    <div class="quill-wrappera">
                        <!-- Create the toolbar container -->
                        <div id="toolbar" class="ql-toolbar ql-snow">
                            {{--<button class="ql-bold">Bold</button>--}}
                            {{--<button class="ql-italic">Italic</button>--}}
                        </div>
                        <div id="editor" class="editor ql-container ql-snow">
                            <div class="ql-editor" id="ql-editor-1" contenteditable="false">

                            </div>
                            <div class="ql-paste-manager" contenteditable="false"></div>
                        </div>
                        {{--<button name="submit" value="submit" id="saveButton" class="btn">Save</button>--}}
                    </div>
                </div>
                <div class="right-document-wrap">
                    <canvas id="the-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(function ($) {
            //if loaded for the first time, load page 1
            var page = '{{$page}}';
            pageLoader(fileFoler, page);
            var contractId = "{{$contract->id}}"
            //load the appropriate page when clicked
            $.ajaxSetup({
                headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')}
            });
            var url = "{{Request::url()}}";
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
                content.annotator('addPlugin', 'Tags');
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
                if(index == page)
                {
                    divClass = "active";
                }else {
                    divClass = "";
                }
                $('#pagelist').append('<a class="{1}" href="{{URL::current()}}?page={0}">{0}</a>&nbsp;'.format(index, divClass));
        }

        //read the url content
        function httpGet(theUrl) {
            var xmlHttp = null;
            xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", theUrl, false);
            xmlHttp.send(null);
            return xmlHttp.responseText;
        }

        var editor = new Quill('#editor', {theme: 'snow',readOnly: true});
        //editor.addModule('toolbar', {container: '#toolbar'});

        function pageLoader(fileFoler, page) {
            //create text and pdf location based on the defined structure
            var textLocation = "/data/{0}/text/{1}.txt".format(fileFoler, page);
            var pdfLocation = "/data/{0}/pages/{1}.pdf".format(fileFoler, page);
            console.log(pdfLocation)
            editor.setHTML(httpGet(textLocation));

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