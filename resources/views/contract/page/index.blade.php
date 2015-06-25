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

            {!! Form::open(['route' => ['contract.page.search', $contract->id], 'method' => 'POST', 'class'=>'form-inline page-search', 'style' => 'width: 421px; margin: 0 auto 23px;']) !!}
            <div class="form-group">
                <div class="input-group">
                    {!! Form::text('q', null, ['class' => 'form-control', 'placeholder' => 'Search...' , 'style' => 'padding:15px; width:280px']) !!}
                </div>
            </div>
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}

             <div id="message" style="padding: 0px 16px"></div>
            <div id="pagination"></div>
            <div class="document-wrap">
            <div class="left-document-wrap" id="annotatorjs">                    
                    <div class="quill-wrapper">
                        <!-- Create the toolbar container -->
                        <div id="toolbar" class="ql-toolbar ql-snow">
                            <button class="ql-bold">Bold</button>
                            <button class="ql-italic">Italic</button>
                        </div>
                        <div id="editor" style="height: 750px" class="editor ql-container ql-snow">
                        </div>
                        <button name="submit" value="submit" id="saveButton" class="btn">Save</button>
                    </div>
                </div>
                <div class="right-document-wrap">
                    <canvas id="pdfcanvas"></canvas>
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
    <script src="{{ asset('js/jquery.simplePagination.js') }}"></script>
    <script src="{{ asset('js/lib/underscore.js') }}"></script>
    <script src="{{ asset('js/lib/backbone.js') }}"></script>
    <script src="{{ asset('js/contractmvc.js') }}"></script>

    <script>
        $(function(){
            function template(obj)
            {
                return '<p style="margin-bottom: 20px; border-bottom:1px solid #E0E0E0; padding-bottom: 20px;">'+obj.text+'<a href="{{route('contract.pages', [$contract->id])}}?page='+obj.page_no+'"> p.'+obj.page_no+'</a></p>';
            }

            $(document).on('click', '.search-cancel', function(){
                $('.right-document-wrap canvas').show();
                $('.right-document-wrap .search').hide();
                $('.page-search').find('input[type=text]').val('');
            });

            $('.page-search').on('submit', function(e){
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    url : form.attr('action'),
                    postType : 'JSON',
                    type : form.attr('method'),
                    data : form.serialize()
                }).done(function(response){

                    $('.right-document-wrap canvas').hide();
                    $('.right-document-wrap .search').hide();
                    var search = "<div style='margin-bottom: 30px;'> <a href='#' class='pull-right search-cancel'><i class='glyphicon glyphicon-remove'></i></a>"
                    total = response.length;
                    if(total > 0)
                    {
                        search += "<h4>Search result for '"+form.find('input[type=text]').val()+"'</h4>";
                        search += "<p>Total "+total+" result(s) found.</p></div>";

                        $.each(response, function( index, value ) {
                            search +=template(value);
                        });
                    }
                    else{
                        search += "<h4>Result not found for '"+form.find('input[type=text]').val()+"</h4>'</div>";
                    }

                    $('.right-document-wrap').append('<div class="search">'+search+'</div>');
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
    }).render();

    $('#saveButton').click(function (el) {
        pageView.saveClicked();
    });

    </script>
@stop
