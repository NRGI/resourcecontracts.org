@extends('layout.app-full')
@section('css')
    <link rel="stylesheet" href="{{ url('scripts/lib/annotator/annotator.css') }}" />
    <link rel="stylesheet" href="{{ url('css/contract-view.css') }}" />
    <link rel="stylesheet" href="{{ url('css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ url('css/jquery-ui.css') }}" />
    <style>
        .ui-slider-horizontal .ui-slider-range-min {
            background: none;
        }
        .pdf-zoom-options > div{
            padding: 0px !important;
            border: none;
            background-position: 0 3px;
            height: 30px;
        }
        .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default
        {
            border-radius: 50%;
            background: #333333;
            outline: none;
        }

        .ui-slider-horizontal .ui-slider-handle
        {
            margin-left: -0.4em;
            height: 15px;
            width: 15px;
            margin-top: 1px;
        }

        .ui-widget-content .ui-state-focus {
            background-color: #4797DF !important;
        }

    </style>
@stop
@section('content')
    <div id="content"></div>
@stop
@section('script')
    <script src="{{ url('scripts/lib/jquery.js') }}"></script>
    <script src="{{ url('js/select2.full.js') }}"></script>
    <script src="{{ url('scripts/lib/underscore.js') }}"></script>
    <script src="{{ url('scripts/lib/backbone.js') }}"></script>

    <script src="{{ url('scripts/lib/director.min.js') }}"></script>
    
    <script src="{{ url('scripts/lib/react/react-with-addons.js') }}"></script>
    <script src="{{ url('scripts/lib/react/JSXTransformer.js') }}"></script>

    <script src="{{ url('scripts/lib/pdfjs/pdf.js') }}"></script>
    <script src="{{ url('scripts/lib/pdfjs/pdf.worker.js') }}"></script>

    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/react.pdf.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/react.waypoint.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/pdf.view.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/text.view.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/text.search.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/annotations.view.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/metadata.view.js') }}"></script>

    <script src="{{ url('scripts/contract.view.custom/rc.utils.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/pages.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/annotations.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/search.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/metadata.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/contract.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/pdf.js') }}"></script>

    <script src="{{ url('scripts/lib/annotator/annotator-full.min.js') }}"></script>
    <script src="{{ url('scripts/lib/annotator.plugin.annotorious.js') }}"></script>

    <script src="{{ url('scripts/contract.view.custom/annotation/annotator.utils.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/annotation/rc.annotator.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/annotation/annotator.plugin.categories.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/annotation/annotator.plugin.viewer.js') }}"></script>    
    <script src="{{ url('scripts/contract.view.custom/annotation/annotator.plugin.event.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/annotation/annotator.plugin.section.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/annotation/annotator.plugin.parentannotation.js') }}"></script>
    <script src="{{url('scripts/lib/jquery-ui.js')}}"></script>

    <script type="text/jsx">

      var debug = function() {
        var DEBUG = false;
        if(DEBUG) {
          console.log("-----");
          for (var i=0; i < arguments.length; i++) {
            console.log(arguments[i]);
          }
        }
      }

      var back_url = '{!!$back!!}';
      var app_url = '{{url()}}';
      var contractTitle = "{{$contract->title}}";
      var contractApp = new ContractApp({
        contract_id: '{{$contract->id}}',
        total_pages: '{{$contract->pages->count()}}',
        allpage_url: "{{route('contract.allpage.get', ['id'=>$contract->id])}}",
        annotation_url: "{{route('contract.annotations', ['id'=>$contract->id])}}",
        search_url: "{{route('contract.page.search', ['id'=>$contract->id])}}",
        categories_codelist: {!! json_encode(trans("codelist/annotation.annotation_category")) !!},
        page_no: 1
      });

      var pagesCollection = new ViewerPageCollection();
      pagesCollection.url = contractApp.getAllPageUrl();
      pagesCollection.fetch({reset: true});
      pagesCollection.on("reset", function() {
        contractApp.trigger("change:pdf_url");
      });

      var annotationsCollection = new AnnotationsCollection();
      annotationsCollection.url = contractApp.getAllAnnotationsUrl();
      annotationsCollection.fetch({reset: true});

      var searchResultsCollection = new SearchResultsCollection();
      searchResultsCollection.url = contractApp.getSearchUrl();

      var pdfPage = new PdfPage({
        contractApp: contractApp
      });
      
      /**
      * @jsx React.DOM
      */

      var MainApp = React.createClass({
        getInitialState: function() {
          return {
            currentView: 'pdf'
          }
        },
        text: function(page_no, annotation_id) {
          contractApp.setView("text");
          contractApp.setCurrentPage(contractApp.getCurrentPage());
          contractApp.resetSelectedAnnotation();
          if(page_no) {
            contractApp.setCurrentPage(page_no);
          }
          if(annotation_id) {
            contractApp.setSelectedAnnotation(annotation_id);
          }
          contractApp.trigger("update-text-pagination-page", contractApp.getCurrentPage());
          this.forceUpdate();
         contractApp.trigger('scroll-to-text-page');
        },
        pdf: function(page_no, annotation_id) {
          contractApp.setView("pdf");
          contractApp.trigger("update-pdf-pagination-page", contractApp.getCurrentPage());
          if(page_no) {
            contractApp.setCurrentPage(page_no);
          }
          if(annotation_id) {
            contractApp.setSelectedAnnotation(annotation_id);
          } else {
            contractApp.resetSelectedAnnotation();
          }
          this.forceUpdate();
        },
        search: function(query) {
          contractApp.setView("search");
          contractApp.setSearchQuery(query);
          searchResultsCollection.fetch({
            searchTerm: query,
            reset: true
          });
          this.forceUpdate();
        },
        meta: function(action) {
          this.forceUpdate();
        },
        componentDidUpdate: function() {
          // viewerCurrentPage.set({"page_no": 8});
        },
       componentWillMount: function() {
          var router = Router({
            '/text': this.text,
            '/text/page/:page_no': this.text,
            '/text/page/:page_no/annotation/:annotation_id': this.text,
            '/pdf': this.pdf,
            '/pdf/page/:page_no': this.pdf,
            '/pdf/page/:page_no/annotation/:annotation_id': this.pdf,
            '/search/:query': this.search,
            '/meta/:action': this.meta
          });
          router.init();
        },    
        getStyle: function(showFlag) {
          var style = { display: "none" };
          if(showFlag) style.display = "block";
          return style;
        },
        render: function() {
          return (
            <div className="main-app">
              <div className="title-wrap">
                <a className="back" href={back_url}></a>
                <span>{htmlDecode(contractTitle)}</span>
              </div>
              <div className="head-wrap">
                <TextSearchForm
                  style={this.getStyle(contractApp.isViewVisible("TextSearchForm"))} />
                <NavigationView
                  contractApp={contractApp} />
                <TextPaginationView 
                  style={this.getStyle(contractApp.isViewVisible("TextPaginationView"))} 
                  contractApp={contractApp}
                  pagesCollection={pagesCollection} />
                <PdfPaginationView 
                  style={this.getStyle(contractApp.isViewVisible("PdfPaginationView"))}
                  contractApp={contractApp} />
                <PdfZoom 
                  style={this.getStyle(contractApp.isViewVisible("PdfZoom"))}
                  contractApp={contractApp} />
                <MetadataToggleButton 
                  style={this.getStyle(contractApp.getShowMeta())}                  
                  contractApp={contractApp} />
              </div>
              <div className="document-wrap">
                <AnnotationsViewer 
                  style={this.getStyle(contractApp.isViewVisible("AnnotationsViewer"))}
                  contractApp={contractApp}
                  annotationsCollection={annotationsCollection} />
                <TextSearchResultsList
                  style={this.getStyle(contractApp.isViewVisible("TextSearchResultsList"))}
                  contractApp={contractApp}
                  searchResultsCollection={searchResultsCollection} />
                <TextViewer 
                  style={this.getStyle(contractApp.isViewVisible("TextViewer"))}
                  contractApp={contractApp}
                  pagesCollection={pagesCollection} />
                <PdfViewer 
                  pdfPage={pdfPage}
                  style={this.getStyle(contractApp.isViewVisible("PdfViewer"))}                  
                  contractApp={contractApp}
                  showAnnotations="true" />
              </div>
            </div>
          );
        }
      });
      
      React.render(
        <MainApp />,
        document.getElementById('content')
      );
    </script>

@stop


