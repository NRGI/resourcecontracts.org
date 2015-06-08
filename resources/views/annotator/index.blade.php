@extends('layout.app')
@section('content')
    <script src="{{ URL::asset('js/lib/quill/quill.js') }}"></script>
    <script src="{{ URL::asset('js/lib/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ URL::asset('js/lib/pdfjs/pdf.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/style.css') }}"/>
    <style>
        .container {
            display: flex;
        }

        .fixed {
            width: 500px;
        }

        .flex-item {
            flex-grow: 1;
        }
    </style>
    <div class="container ">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Contract Document </div>
                    <div class="panel-body annotate">
                        <div id="pagelist">
                        </div>
                        <div class="container">
                            <div class="fixed">
                                <div class="quill-wrapper">
                                    <!-- Create the toolbar container -->
                                    {{--<div id="toolbar">--}}
                                    {{--<button class="ql-bold">Bold</button>--}}
                                    {{--<button class="ql-italic">Italic</button>--}}
                                    {{--</div>--}}
                                    <div id="editor" class="editor">
                                    </div>
                                    {{--<button name="submit" value="submit" id="saveButton">Save</button>--}}
                                </div>
                            </div>
                            <div class="flex-item">
                                <input type="hidden" class="document_id" id="document_id" value="1">
                                <canvas id="the-canvas" style="border:1px solid black;"/>
                            </div>
                        </div>
                        <script>
                            jQuery(function ($) {
                                //if loaded for the first time, load page 1
                                pageLoader(fileFoler, 1);
                                //load the appropriate page when clicked
                                $('#pagelist a').click(function () {
                                    var page = this.text.trim();
                                    pageLoader(fileFoler, page);
                                    console.log($('#document_id').val(page));

                                    setupAnnotator(content, 100);
                                    console.log("testing here");
                                    //call search for page
                                });

                                $('#saveButton').click(function () {
                                    var htmlContent = editor.getHTML();
                                    console.log(htmlContent);
                                })
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
                                            'contract': '10',
                                            'document_page_no': documentId
                                        },
                                        loadFromSearch: {
                                            'contract': '10',
                                            'document_page_no': documentId,
                                            'url': url
                                        }
                                    });
                                    content.annotator('addPlugin', 'Tags');
                                };
                                setupAnnotator(content, 1);

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

                            var totalPages = 21;
                            var fileFoler = "in";

                            //fill the page numbers
                            for (var index = 1; index <= totalPages; ++index) {
                                //$('#pagelist').append('<a href="#{0}">{0}</a>&nbsp;'.format(index));
                            }

                            //read the url content
                            function httpGet(theUrl) {
                                var xmlHttp = null;
                                xmlHttp = new XMLHttpRequest();
                                xmlHttp.open("GET", theUrl, false);
                                xmlHttp.send(null);
                                return xmlHttp.responseText;
                            }

                            var editor = new Quill('#editor', {theme: 'snow'});
                            editor.addModule('toolbar', {container: '#toolbar'});

                            function pageLoader(fileFoler, page) {
                                page = 19;
                                //create text and pdf location based on the defined structure
                                var textLocation = "/data/{0}/text/{1}.txt".format(fileFoler, page);
                                var pdfLocation = "/data/{0}/pages/{1}.pdf".format(fileFoler, page);
                                editor.setHTML(httpGet(textLocation));

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
                            //if loaded for the first time, load page 1
                            pageLoader(fileFoler, 19);
                        </script>
                    </div>
                </div>
            </div>
@endsection

