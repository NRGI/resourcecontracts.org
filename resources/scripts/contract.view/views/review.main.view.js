var contractApp = new ContractApp(contractAppSetting);
var searchResultsCollection = new SearchResultsCollection();
searchResultsCollection.url = contractApp.getSearchUrl();

var pdfPage = new PdfPage({
    contractApp: contractApp
});

var api = {
    save: saveApi,
    load: loadApi,
    publish: publishApi
};

var MainApp = React.createClass({
    getInitialState: function () {
        return {
            currentView: 'text'
        }
    },
    default: function () {
        contractApp.setView("text");
        if (!contractApp.getCurrentPage()) {
            contractApp.setCurrentPage(1);
        }
        this.forceUpdate();
    },
    text: function (page_no) {
        contractApp.setView("text");
        if (page_no) {
            contractApp.setCurrentPage(page_no);
        }
        this.forceUpdate();
    },
    search: function (query) {
        contractApp.setView("search");
        contractApp.setSearchQuery(query);
        searchResultsCollection.fetch({
            searchTerm: query,
            reset: true
        });
        this.forceUpdate();
    },
    componentDidUpdate: function () {
        // viewerCurrentPage.set({"page_no": 8});
    },
    componentDidMount: function () {
        var router = Router({
            '/': this.default,
            '/text': this.text,
            '/text/page/:page_no': this.text,
            '/search/:query': this.search,
        });
        router.init();
        contractApp.trigger("change:page_no");
    },
    getStyle: function (showFlag) {
        var style = {display: "none"};
        if (showFlag) style.display = "block";
        return style;
    },
    render: function () {
        return (
            <div className="main-app">
                <div className="title-wrap">
                    <a className="back" href={back_url}></a>
                    <span>{htmlDecode(contractTitle)}</span>
                </div>
                <div className="head-wrap">
                    <TextSearchForm contractApp={contractApp}/>
                    <TextPaginationView
                        contractApp={contractApp}/>
                    <PdfZoom
                        contractApp={contractApp}/>
                </div>
                <div className="document-wrap">
                    <TextSearchResultsList
                        style={this.getStyle(contractApp.isViewVisible("TextSearchResultsList"))}
                        contractApp={contractApp}
                        searchResultsCollection={searchResultsCollection}/>
                    <TextEditorContainer
                        style={this.getStyle(true)}
                        contractApp={contractApp}
                        showAnnotations="false"
                        saveApi={api.save}
                        publishApi={api.publish}
                        loadApi={api.load}/>
                    <PdfViewer
                        pdfPage={pdfPage}
                        style={this.getStyle(true)}
                        contractApp={contractApp}
                        showAnnotations="false"/>
                </div>
            </div>
        );
    }
});

React.render(
    <MainApp />,
    document.getElementById('content')
);
