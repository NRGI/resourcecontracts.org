@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> Editing {{$contract->metadata->project_title}}   <a class="btn btn-default pull-right" href="{{route('contract.index')}}">Back to home</a> </div>

        <div class="view-wrapper" style="background: #F6F6F6">
            <div id="pagelist"></div>
            <div class="document-wrap">
                <div class="left-document-wrap">
                    <div class="quill-wrapper">
                        <!-- Create the toolbar container -->
                        <div id="toolbar" class="ql-toolbar ql-snow">
                            <button class="ql-bold">Bold</button>
                            <button class="ql-italic">Italic</button>
                        </div>
                        <div id="editor" class="editor ql-container ql-snow">
                            <div class="ql-editor" id="ql-editor-1" contenteditable="true">

                            </div>
                            <div class="ql-paste-manager" contenteditable="true"></div>
                        </div>
                        <button name="submit" value="submit" id="saveButton" class="btn">Save</button>
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
    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>
    <script>
        jQuery(function ($) {

            //if loaded for the first time, load page 1
            pageLoader(fileFoler, 1);
            //load the appropriate page when clicked
            $('#pagelist a').click(function () {
                var page = this.text.trim();
                $(this).parent().find('a').removeClass('active');
                $(this).addClass('active');

                pageLoader(fileFoler, page);
                console.log($('#document_id').val(page));

                setupAnnotator(content, 100);
                console.log("testing here");
                //call search for page
            });

            $('#saveButton').click(function () {
                var htmlContent = editor.getHTML();
                var page = $('#pagelist a.active').text().trim();
                $.ajax({
                    url: '{{route('contract.page.store', ['id'=>$contract->id])}}',
                    data: {'text': htmlContent, 'page': page},
                    type: 'POST'
                }).done(function (response) {
                    alert('Saved');
                })
            });
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

        var totalPages = {{$contract->pages->count()}};
        var fileFoler = '{{ $contract->id }}';

        //fill the page numbers
        for (var index = 1; index <= totalPages; ++index) {
            if (index == 1)
                $('#pagelist').append('<a class="active" href="#{0}">{0}</a>&nbsp;'.format(index));
            else
                $('#pagelist').append('<a href="#{0}">{0}</a>&nbsp;'.format(index));

        }

        var editor = new Quill('#editor', {theme: 'snow'});
        editor.addModule('toolbar', {container: '#toolbar'});


        //read the url content
        function loadPageText(page) {
            var reText = '';
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


        function pageLoader(fileFoler, page) {
            //create text and pdf location based on the defined structure
            var pdfLocation = "/data/{0}/pages/{1}.pdf".format(fileFoler, page);
            console.log(pdfLocation)
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
