@extends('layout.app-full')
@section('css')
    <link rel="stylesheet" href="{{ url('scripts/lib/annotator/annotator.css') }}">
    <link rel="stylesheet" href="{{ url('css/contract-view.css') }}">
    <link rel="stylesheet" href="{{ url('css/contract-review.css') }}">
@stop
@section('content')
    <div id="content"></div>
@endsection
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
    <!-- // <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/pdf.view.js') }}"></script> -->
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/pdf.view.for.text.edit.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/text.view.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/text.edit.js') }}"></script>
    <script type="text/jsx" src="{{ url('scripts/contract.view.custom/views/text.search.js') }}"></script>

    <script src="{{ url('scripts/contract.view.custom/rc.utils.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/pages.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/annotations.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/search.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/contract.js') }}"></script>
    <script src="{{ url('scripts/contract.view.custom/models/pdf.js') }}"></script>

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
        page_no: 1
      });

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
            currentView: 'text'
          }
        },
        default: function() {
          contractApp.setView("text");
          if(!contractApp.getCurrentPage()) {
            contractApp.setCurrentPage(1);
          }
          this.forceUpdate();
        },
        text: function(page_no) {
          contractApp.setView("text");
          if(page_no) {
            contractApp.setCurrentPage(page_no);
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
        componentDidUpdate: function() {
          // viewerCurrentPage.set({"page_no": 8});
        },
        componentDidMount: function() {
          var router = Router({
            '/': this.default,
            '/text': this.text,
            '/text/page/:page_no': this.text,
            '/search/:query': this.search,
          });
          router.init();
          contractApp.trigger("change:page_no");
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
                <TextSearchForm />
                <TextPaginationView
                  contractApp={contractApp} />
                <PdfZoom
                  contractApp={contractApp} />
              </div>
              <div className="document-wrap">
                <TextSearchResultsList
                  style={this.getStyle(contractApp.isViewVisible("TextSearchResultsList"))}
                  contractApp={contractApp}
                  searchResultsCollection={searchResultsCollection} />
                <TextEditorContainer
                  style={this.getStyle(true)}
                  contractApp={contractApp}
                  showAnnotations="false"
                  saveApi="{{route('contract.page.store', ['id'=>$contract->id])}}"
                  loadApi="{{route('contract.page.get', ['id'=>$contract->id])}}" />
                <PdfViewer
                  pdfPage={pdfPage}
                  style={this.getStyle(true)}
                  contractApp={contractApp}
                  showAnnotations="false" />
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


