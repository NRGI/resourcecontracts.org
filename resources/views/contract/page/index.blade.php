@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> Editing {{$contract->metadata->project_title}}   <a class="btn btn-default pull-right" href="{{route('contract.index')}}">Back to home</a> </div>

        <div class="view-wrapper" style="background: #F6F6F6">
            <div id="pagelist"></div>
            <div class="document-wrap">
            <div class="left-document-wrap annotate">
                <span id="message"></span>
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
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/lib/pdfjs/pdf.js') }}"></script>
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

    var contract = {
        id: {{$contract->id}},
        filesBaseDir: {{$contract->id}},
        totalPages: {{$contract->pages->count()}},
        currentPage: {{$page}},
        getPdfLocation: function() { return "/data/{0}/pages/{1}.pdf".format(this.filesBaseDir, this.currentPage);},
        viewUrl: "{{route('contract.pages', ['id'=>$contract->id])}}",
        textLoadAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}",
        textSaveAPI: "{{route('contract.page.store', ['id'=>$contract->id])}}",
        annotationAPI: "{{route('contract.page.get', ['id'=>$contract->id])}}",
        canEdit: {{$canEdit}},
        canAnnotate: {{$canAnnotate}},
        getAction: function() {
            if(this.canEdit) return "action=edit";
            else if(this.canAnnotate) return "action=annotate";
            else return "";
        }
    };

    var textEditor = {
        init: function(contract) {
            this.contract = contract;
            this.textUpdated = false;
            var options = {theme: 'snow'};
            if(!this.contract.canEdit) {
                options.readOnly = true;
                $('#saveButton').hide();
            }

            this.editor = new Quill('#editor', options);
            this.editor.addModule('toolbar', {container: '#toolbar'});
            
            this.editor.on('text-change', function(delta, source) {
              if (source == 'api') {
                //none
              } else if (source == 'user') {
                this.textUpdated = true;
                $('#message').text('Text updated.');
              }
            });
        },
        load: function() {
            var reText = '';
            var that = this;
            $.ajax({
                url: this.contract.textLoadAPI,
                data: {'page': this.contract.currentPage},
                type: 'GET',
                dataType: 'JSON',
                success: function (response) {
                    that.editor.setHTML(response.message);
                }
            });
        },
        save: function() {
            var htmlContent = this.editor.getHTML();
            $.ajax({
                url: this.contract.textSaveAPI,
                data: {'text': htmlContent, 'page': this.contract.currentPage},
                type: 'POST'
            }).done(function (response) {
                this.textUpdated = false;
                $('#message').text('saved.');
            });        
        },
    };

    var pdfViewer = {
        load: function(contract) {
            PDFJS.workerSrc = '/js/lib/pdfjs/pdf.worker.js';
            PDFJS.getDocument(contract.getPdfLocation()).then(function (pdf) {
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
    };

    var pagination = {
        init: function(contract) {
            this.contract = contract;
        },
        show: function() {
            var that = this;
            $('#pagelist').twbsPagination({
                totalPages: this.contract.totalPages,
                visiblePages: 10,
                startPage: this.contract.currentPage,
                onPageClick: function (event, page) {
                    location.href = '{0}?{1}&page={2}'.format(that.contract.viewUrl, that.contract.getAction(), page);
                }
            });             
        }
    };

    var contractAnnotator = {
        init: function(contract) {
            this.contract = contract;
            var options = (contract.canAnnotate)?{readOnly: false}:{readOnly: true};
            this.content = $('.annotate').annotator(options);     
            this.content.annotator('addPlugin', 'Tags');
            this.availableTags = {!! json_encode(config('nrgi.annotation_tags')) !!};
        },
        setup: function(page) {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                annotationData: {
                    'url': this.contract.annotationAPI,
                    'contract': this.contract.id,
                    'document_page_no': this.contract.currentPage
                },
                loadFromSearch: {
                    'url': this.contract.annotationAPI,
                    'contract': this.contract.id,
                    'document_page_no': this.contract.currentPage
                }
            }); 
            this.content.data('annotator').plugins.Tags.input.autocomplete({
                source: this.availableTags,
                multiselect: true
            });
        },
    };

    jQuery(function ($) {
        textEditor.init(contract)
        textEditor.load();
        pdfViewer.load(contract);
        pagination.init(contract)
        pagination.show();
        contractAnnotator.init(contract);
        contractAnnotator.setup();
        $('#saveButton').click(function () {
            textEditor.save();
        });
    });

    </script>
@stop
