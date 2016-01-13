var PdfPaginationView = React.createClass({
    getInitialState: function () {
        return {
            visiblePage: 1,
            totalPages: 0
        }
    },

    changePage: function (page_no) {
        this.refs.userInput.getDOMNode().value = page_no;
        this.setState({visiblePage: page_no});
        this.props.contractApp.setCurrentPage(page_no);
    },
    clickPrevious: function (e) {
        e.preventDefault();
        if (this.state.visiblePage > 1) {
            this.changePage(this.state.visiblePage - 1);
            this.props.contractApp.setPrevClick(true);
        }
    },
    clickNext: function (e) {
        e.preventDefault();
        if (this.state.visiblePage < this.state.totalPages) {
            this.changePage(this.state.visiblePage + 1);
        }
    },
    handleKeyDown: function (e) {
        if (e.keyCode == 13) {
            var inputPage = parseInt(this.refs.userInput.getDOMNode().value);
            if (inputPage > 0 && inputPage <= this.state.totalPages) {
                this.changePage(inputPage);
            } else {
                this.changePage(this.state.visiblePage);
            }
        }
    },
    componentWillMount: function () {
        this.setState({visiblePage: this.props.contractApp.getCurrentPage()});
    },
    componentDidMount: function () {
        var self = this;
        self.setState({totalPages: self.props.contractApp.getTotalPages()});
        this.props.contractApp.on("update-pdf-pagination-page", function (page_no) {
            self.refs.userInput.getDOMNode().value = page_no;
            self.setState({visiblePage: page_no});
        });

        this.refs.userInput.getDOMNode().value = this.state.visiblePage;
    },
    render: function () {
        return (
            <div className="pdf-pagination pagination" style={this.props.style}>
                <a href="#" className="previous" onClick={this.clickPrevious}>Previous</a>
                <input type="text" className="goto" ref="userInput" onKeyDown={this.handleKeyDown}/>
                <a href="#" className="next" onClick={this.clickNext}>Next</a> of {this.state.totalPages}
            </div>
        );
    }
});

var PdfZoom = React.createClass({
    getInitialState: function () {
        return {
            scale: '1'
        }
    },
    handleClick: function (int) {
        this.setState({scale: int});
        this.props.contractApp.setPdfScale(int);
    },
    componentDidMount: function () {
        var self = this;
            var slider = $('#slider').slider({
                min: 1,
                max: 1.5,
                range: "min",
                step: 0.25,
                value: 1,
                slide: function (event, ui) {
                    self.handleClick(ui.value);
                }
        });
    },
    render: function () {
        var selectedClass = "scale-" + this.state.scale;
        $('.pdf-zoom-options span').removeClass('scale-selected');
        $('.pdf-zoom-options .' + selectedClass).addClass('scale-selected');
        return (
            <div>
                <div className="pdf-zoom-options" style={this.props.style}> Zoom
                    <div id="slider"></div>
                </div>
           </div>
        );
    }
});

var PdfViewer = React.createClass({
    getInitialState: function () {
        return {
            loadAnnotations: false
        };
    },
    componentDidMount: function () {
        this.loadAnnotationsFlag = false;
        var self = this;
        if (this.props.pagesCollection) {
            this.props.pagesCollection.on("reset", function () {
                debug("pdf.view.js pagesCollection reset called: triggering change:page_no");
                self.props.contractApp.trigger("change:page_no");
            });
        }
        this.props.contractApp.on("change:pdfscale", function () {
            self.loadAnnotationsFlag = true;
            self.forceUpdate();
        });
        this.props.pdfPage.on("change:content", function () {
            self.loadAnnotationsFlag = true;
            // self.setState({loadAnnotations: true});
        });
    },
    render: function () {
        var page_no = this.props.contractApp.getCurrentPage();
        var pdfUrl = this.props.contractApp.getPdfUrl();
        return (
            <div className="pdf-viewer pdf-annotator" style={this.props.style}>
                <Pdf
                    contractApp={this.props.contractApp}
                    pdfPage={this.props.pdfPage}
                    page={1}
                    content={this.props.pdfPage.get("content")}
                    scale={parseFloat(this.props.contractApp.getPdfScale())||1}
                    onPageRendered={this._onPageRendered}
                    renderReady={this.state.renderReady}/>
            </div>
        );
    },
    loadAnnotations: function () {
        if (!this.annotator) {
            this.annotator = new PdfAnnotatorjsView({
                el: ".pdf-annotator",
                api: this.props.contractApp.getLoadAnnotationsUrl(),
                // api: "http://localhost:8009",
                availableTags: ["Country", "Local-Company-Name"],
                // collection: annotationCollection,
                annotationCategories: ["General information", "Country", "Local company name"],
                enablePdfAnnotation: true,
                contractApp: this.props.contractApp
            });
            this.props.contractApp.setAnnotatorInstance(this.annotator);
        }
    },
    _onPageRendered: function () {
        if (this.props.contractApp.getView() === "pdf" && this.loadAnnotationsFlag) {
            if (this.annotator) {
                this.annotator.pageUpdated();
                this.loadAnnotationsFlag = false;
                // this.setState({loadAnnotations: false});
            }
            else if ($(".pdf-viewer").is(":visible")) {
                this.loadAnnotations();
                this.loadAnnotationsFlag = false;
                // this.setState({loadAnnotations: false});
            }
        }
    },
});
