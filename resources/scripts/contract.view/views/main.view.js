var contractApp = new ContractApp(contractAppSetting);

var pagesCollection = new ViewerPageCollection();
pagesCollection.url = contractApp.getAllPageUrl();
pagesCollection.fetch({reset: true});
pagesCollection.on("reset", function () {
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
    getInitialState: function () {
        return {
            currentView: 'pdf'
        }
    },
    text: function (page_no, annotation_id) {
        contractApp.setView("text");
        contractApp.setCurrentPage(contractApp.getCurrentPage());
        contractApp.resetSelectedAnnotation();
        if (page_no) {
            contractApp.setCurrentPage(page_no);
        }
        if (annotation_id) {
            contractApp.setSelectedAnnotation(annotation_id);
        }
        contractApp.trigger("update-text-pagination-page", contractApp.getCurrentPage());
        this.forceUpdate();
        contractApp.trigger('scroll-to-text-page');
    },
    pdf: function (page_no, annotation_id) {
        contractApp.setView("pdf");
        if (page_no) {
            contractApp.setCurrentPage(page_no);
        }
        if (annotation_id) {
            contractApp.setSelectedAnnotation(annotation_id);
        } else {
            contractApp.resetSelectedAnnotation();
        }
        contractApp.trigger("change:page_no");
        contractApp.trigger("update-pdf-pagination-page", contractApp.getCurrentPage());

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
    meta: function (action) {
        this.forceUpdate();
    },
    componentDidUpdate: function () {
        // viewerCurrentPage.set({"page_no": 8});
    },
    componentWillMount: function () {
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
                    <TextSearchForm
                        contractApp={contractApp}
                        style={this.getStyle(contractApp.isViewVisible("TextSearchForm"))}/>
                    <NavigationView
                        contractApp={contractApp}/>
                    <TextPaginationView
                        style={this.getStyle(contractApp.isViewVisible("TextPaginationView"))}
                        contractApp={contractApp}
                        pagesCollection={pagesCollection}/>
                    <PdfPaginationView
                        style={this.getStyle(contractApp.isViewVisible("PdfPaginationView"))}
                        contractApp={contractApp}/>
                    <PdfZoom
                        style={this.getStyle(contractApp.isViewVisible("PdfZoom"))}
                        contractApp={contractApp}/>
                    <MetadataToggleButton
                        style={this.getStyle(contractApp.getShowMeta())}
                        contractApp={contractApp}/>
                </div>
                <div className="document-wrap">
                    <AnnotationsViewer
                        style={this.getStyle(contractApp.isViewVisible("AnnotationsViewer"))}
                        contractApp={contractApp}
                        annotationsCollection={annotationsCollection}/>
                    <TextSearchResultsList
                        style={this.getStyle(contractApp.isViewVisible("TextSearchResultsList"))}
                        contractApp={contractApp}
                        searchResultsCollection={searchResultsCollection}/>
                    <TextViewer
                        style={this.getStyle(contractApp.isViewVisible("TextViewer"))}
                        contractApp={contractApp}
                        pagesCollection={pagesCollection}/>
                    <PdfViewer
                        pdfPage={pdfPage}
                        style={this.getStyle(contractApp.isViewVisible("PdfViewer"))}
                        contractApp={contractApp}
                        showAnnotations="true"/>
                </div>
            </div>
        );
    }
});

React.render(
    <MainApp />,
    document.getElementById('content')
);