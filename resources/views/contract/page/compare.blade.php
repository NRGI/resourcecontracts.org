@extends('layout.app-full')

@section('css')
    <link rel="stylesheet" href="{{ asset('js/lib/quill/quill.snow.css') }}"/>
    <link rel="stylesheet" href="{{ asset('js/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ asset('css/simplePagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">

<style>
.annotation-list {
  display: block;
  position: absolute;
  top: 40px;
  right: 0px;
  width: 400px;
  background-color: #eee;
}
</style>
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading"> @lang('contract.editing') <span>{{$contract->metadata->contract_name or $contract->metadata->project_title}}</span>   <a class="btn btn-default pull-right" href="{{route('contract.show', $contract->id)}}">Back</a> </div>

        <div class="view-wrapper" style="background: #F6F6F6">

            <div id="pagelist"></div>
             <div id="message" style="padding: 0px 16px"></div>
            <div class="document-wrap">
                <div class="left-document-wrap" id="annotatorjs_left">
                    <a id="left" class="btn btn-default pull-right annotation_button" href="#">@lang('annotation.annotations')</a>
                    <div class="quill-wrapper">
                        <div id="pagination_left"></div>
                        <div id="editor_left" class="editor"></div>
                    </div>
                    <div id="annotations_list_left" class="annotation-list" style="display:none"></div>
                </div>
                <div class="right-document-wrap" id="annotatorjs_right">
                    <a id="right" class="btn btn-default pull-right annotation_button" href="#">@lang('annotation.annotations')</a>
                    <div class="quill-wrapper">
                        <div id="pagination_right"></div>
                        <div id="editor_right" class="editor"></div>
                    </div>
                    <div id="annotations_list_right" class="annotation-list" style="display:none"></div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('script')
<script>
var contract1Annotations = [];
</script>
@forelse($contract1['annotations'] as $annotation)
    <script>var tags = [];</script>
   @foreach($annotation['tags'] as $tag)
    <script>tags.push('{{$tag}}');</script>            
    @endforeach
    <script>
    contract1Annotations.push({
        'page': '{{$annotation["page"]}}',
        'text': '{{$annotation["text"]}}',
        'quote': '{{$annotation["quote"]}}',
        'tags': tags
    });
    </script>
@empty 
@endforelse

<script>
var contract2Annotations = [];
</script>
@forelse($contract2['annotations'] as $annotation)
    <script>var tags = [];</script>
   @foreach($annotation['tags'] as $tag)
    <script>tags.push('{{$tag}}');</script>            
    @endforeach
    <script>
    contract2Annotations.push({
        'page': '{{$annotation["page"]}}',
        'text': '{{$annotation["text"]}}',
        'quote': '{{$annotation["quote"]}}',
        'tags': tags
    });
    </script>
@empty 
@endforelse
    <script src="{{ asset('js/lib/quill/quill.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/jquery.simplePagination.js') }}"></script>
    <script src="{{ asset('js/lib/underscore.js') }}"></script>
    <script src="{{ asset('js/lib/backbone.js') }}"></script>
    <script src="{{ asset('js/custom/search.js') }}"></script>
    <script src="{{ asset('js/custom/annotation.js') }}"></script>
    <script src="{{ asset('js/contractmvc.js') }}"></script>
    <script>

    var contractLeft = new Contract({
        id: '{{$contract1["metadata"]->id}}',
        totalPages:'{{$contract1["metadata"]->pages->count()}}',
        currentPage: 1,
        
        editorEl: '#editor_left',
        paginationEl: '#pagination_left',
        annotationslistEl: '#annotations_list_left',
        // pdfviewEl: 'pdfcanvas',
        annotatorjsEl: '#annotatorjs_left',

        // canEdit: false,  
        // canAnnotate: false,

        textLoadAPI: "{{route('contract.page.get', ['id'=>$contract1['metadata']->id])}}", 
        // textSaveAPI: "{{route('contract.page.store', ['id'=>$contract->id])}}",

        annotatorjsAPI: "{{route('contract.page.get', ['id'=>$contract1['metadata']->id])}}"
    });

    var annotationsCollection = new MyAnnotationCollection()
    contract1Annotations.forEach(function(annotationData) {
        annotationsCollection.add(annotationData);
    });


    var pageViewLeft = new PageView({
        pageModel: contractLeft.getPageModel(),
        paginationView: new PaginationView({
            paginationEl: contractLeft.getPaginationEl(), 
            totalPages: contractLeft.getTotalPages(), 
            pageModel: contractLeft.getPageModel()
        }),
        textEditorView: new TextEditorView({
            editorEl: contractLeft.getEditorEl(), 
            pageModel: contractLeft.getPageModel()
        }),
        // pdfView: new PdfView({
        //     pdfviewEl: contract.getPdfviewEl(),
        //     pageModel: contract.getPageModel()
        // }),
        annotatorjsView: new AnnotatorjsView({
            annotatorjsEl: contractLeft.getAnnotatorjsEl(),
            pageModel: contractLeft.getPageModel(),
            contractModel: contractLeft,
            tags:{!! json_encode(trans("codelist/annotationTag.annotation_tags")) !!}
        }),
        annotationsListView: new AnnotationsListView({
            annotationslistEl: contractLeft.getAnnotationsListEl(),
            collection: annotationsCollection,
            pageModel: contractLeft.getPageModel()
        })
    }).render();    

    $('.annotation_button').click(function() {
        if(this.id == "left") pageViewLeft.toggleAnnotationList();
        if(this.id == "right") pageViewRight.toggleAnnotationList();
        // $(el).toggle();
    });


    var contractRight = new Contract({
        id: '{{$contract2["metadata"]->id}}',
        totalPages:'{{$contract2["metadata"]->pages->count()}}',
        currentPage: 1,

        editorEl: '#editor_right',
        paginationEl: '#pagination_right',
        annotationslistEl: '#annotations_list_right',
        annotatorjsEl: '#annotatorjs_right',

        textLoadAPI: "{{route('contract.page.get', ['id'=>$contract2['metadata']->id])}}", 
        // textSaveAPI: "{{route('contract.page.store', ['id'=>$contract->id])}}",

        annotatorjsAPI: "{{route('contract.page.get', ['id'=>$contract2['metadata']->id])}}"
    });

    var annotationsCollection2 = new MyAnnotationCollection()
    contract2Annotations.forEach(function(annotationData) {
        annotationsCollection2.add(annotationData);
    });


    var pageViewRight = new PageView({
        pageModel: contractRight.getPageModel(),
        paginationView: new PaginationView({
            paginationEl: contractRight.getPaginationEl(), 
            totalPages: contractRight.getTotalPages(), 
            pageModel: contractRight.getPageModel()
        }),
        textEditorView: new TextEditorView({
            editorEl: contractRight.getEditorEl(), 
            pageModel: contractRight.getPageModel()
        }),
        annotatorjsView: new AnnotatorjsView({
            annotatorjsEl: contractRight.getAnnotatorjsEl(),
            pageModel: contractRight.getPageModel(),
            contractModel: contractRight,
            tags:{!! json_encode(trans("codelist/annotationTag.annotation_tags")) !!}
        }),
        annotationsListView: new AnnotationsListView({
            annotationslistEl: contractRight.getAnnotationsListEl(),
            collection: annotationsCollection2,
            pageModel: contractRight.getPageModel()
        })

    }).render(); 

    </script>
@stop
