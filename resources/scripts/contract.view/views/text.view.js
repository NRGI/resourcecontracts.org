var NavigationView = React.createClass({
    render: function () {
        if (this.props.contractApp.getView() === "pdf") {
            pdfClass = "active";
            textClass = "";
        } else {
            textClass = "active";
            pdfClass = "";
        }
        return (
            <div className="navigation">
                <a href="#/text" className={textClass}>{LANG.text}</a>
                <a href="#/pdf" className={pdfClass}>{LANG.pdf}</a>
            </div>
        );
    }
});

var TextPaginationView = React.createClass({
    getInitialState: function () {
        return {
            visiblePage: 1,
            totalPages: 0
        }
    },
    changePage: function (page_no) {
        this.refs.userInputText.getDOMNode().value = page_no;
        this.props.contractApp.setCurrentPage(page_no);
        this.setState({visiblePage: page_no});
        this.props.contractApp.triggerScrollToTextPage();
        // this.props.contractApp.trigger("scroll-to-page");
        // this.props.currentPage.set({"page_no": page_no});
        // this.props.currentPage.trigger("scroll-to-page");
    },
    clickPrevious: function (e) {
        e.preventDefault();
        if (this.state.visiblePage > 1) {
            this.changePage(this.state.visiblePage - 1);
            if (this.props.contractApp.getView() == 'pdf') {
                this.props.contractApp.setPrevClick(false);
            } else {
                this.props.contractApp.setPrevClick(true);
            }
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
            var inputPage = parseInt(this.refs.userInputText.getDOMNode().value);
            if (inputPage > 0 && inputPage <= this.state.totalPages) {
                this.changePage(inputPage);
            } else {
                this.changePage(this.state.visiblePage);
            }
        }
    },
    componentDidMount: function () {
        var self = this;
        self.setState({totalPages: self.props.contractApp.getTotalPages()});

        this.props.contractApp.on("update-text-pagination-page", function (page_no) {
            self.refs.userInputText.getDOMNode().value = page_no;
            self.setState({visiblePage: page_no});
            self.props.contractApp.setCurrentPage(page_no);
            self.setState({visiblePage: page_no});
        });

        this.refs.userInputText.getDOMNode().value = this.state.visiblePage;
    },
    render: function () {
        return (
            <div className="text-pagination pagination" style={this.props.style}>
                <a href="#" className="previous" onClick={this.clickPrevious}>{LANG.previous}</a>
                <input type="text" className="goto" ref="userInputText" onKeyDown={this.handleKeyDown}/>
                <a href="#" className="next" onClick={this.clickNext}>{LANG.next}</a>
                {LANG.of} {this.state.totalPages}
            </div>
        );
    }
});

var TextPageView = React.createClass({
    getInitialState: function () {
        return {
            originalHtml: "",
            searchresultsHtml: ""
        };
    },
    _onEnter: function (msg, e) {

        if (!this.props.contractApp.isPrevClick()) {
            this.props.contractApp.triggerUpdateTextPaginationPage(this.props.page.get("page_no"));
        }

        this.props.contractApp.setPrevClick(false);
    },
    _onLeave: function (e) {

    },
    handleClick: function (event) {
        this.props.contractApp.setCurrentPage(this.props.page.get("page_no"));
    },
    sanitizeTxt: function (text) {
        //replace the <  and > with &lt;%gt if they are not one of the tags below
        text = text.replace(/(<)(\/?)(?=span|div|br)([^>]*)(>)/g, "----lt----$2$3----gt----");
        text = text.replace(/</g, "&lt;");
        text = text.replace(/>/g, "&gt;");
        text = text.replace(/----lt----/g, "<");
        text = text.replace(/----gt----/g, ">");
        return text;
    },
    highlightSearchQuery: function (text, highlightword) {
        highlightword = decodeURI(highlightword);
        var re = new RegExp("(" + highlightword + ")", "gi");
        return text.replace(re, "<span class='search-highlight-word'>$1</span>");
    },
    componentDidMount: function () {
        var self = this;
        this.props.contractApp.on("searchresults:close", function () {
            self.setState({
                searchresultsHtml: ""
            });
        });
        this.props.contractApp.on("searchresults:ready", function () {
            if (self.props.contractApp.getSearchQuery()) {
                var originalHtml = self.sanitizeTxt(self.props.page.get('text'));
                // var originalHtml = (self.state.originalHtml !== "")?self.state.originalHtml:React.findDOMNode(self.refs.text_content).innerHTML;;
                var searchresultsHtml = self.highlightSearchQuery(originalHtml, self.props.contractApp.getSearchQuery());
                if (!self.state.originalHtml) {
                    self.setState({
                        originalHtml: originalHtml,
                        searchresultsHtml: searchresultsHtml
                    });
                } else {
                    self.setState({
                        searchresultsHtml: searchresultsHtml
                    });
                }
            }
        });
    },
    render: function () {
        var text = "";
        if (!this.state.originalHtml) {
            text = this.sanitizeTxt(this.props.page.get('text'));
        } else {
            if (this.state.searchresultsHtml) {
                text = this.state.searchresultsHtml;
            } else {
                text = this.state.originalHtml;
            }
        }
        var page_no = this.props.page.get('page_no');
        var threshold = page_no == 1 ? 0 : -0.4;
        return (
            <span className={page_no} onClick={this.handleClick}>
                <span>{page_no}</span>
                <span ref="text_content" dangerouslySetInnerHTML={{__html: text}}/>
                <Waypoint
                    onEnter={this._onEnter.bind(this, "enter" + page_no)}
                    onLeave={this._onLeave}
                    threshold={threshold}/>
            </span>
        );
    }
});

var TextViewer = React.createClass({
    getInitialState: function () {
        return {}
    },
    handleClickWarning: function (e) {
        e.preventDefault();
        $(e.target).parent().hide(500);
    },
    loadAnnotations: function (force) {
        if (!this.annotator) {
            this.annotator = new AnnotatorjsView({
                el: ".text-annotator",
                api: this.props.contractApp.getLoadAnnotationsUrl(),
                availableTags: ["Country", "Local-Company-Name"],
                // collection: annotationCollection,
                annotationCategories: ["General information", "Country", "Local company name"],
                enablePdfAnnotation: false,
                contractApp: this.props.contractApp,
                publishApi: this.props.publishApi,
            });
            this.props.contractApp.setAnnotatorInstance(this.annotator);
        }
    },
    scrollToPage: function (page) {
        if ($('.' + page).offset()) {
            var pageOffsetTop = $('.' + page).offset().top;
            var parentTop = $('.text-annotator ').scrollTop();
            var parentOffsetTop = $('.text-annotator').offset().top
            $('.text-annotator').animate({scrollTop: parentTop - parentOffsetTop + pageOffsetTop}, 100);
        }
    },
    componentDidMount: function () {
        var self = this;

        this.props.contractApp.on("annotationUpdated", function () {
            if (self.annotator) {
                var that = self;
                setTimeout(function () {
                    that.annotator.reload()
                }, 300);
            }
        });


        this.props.contractApp.on("annotationCreated", function () {
            if (self.annotator) {
                var that = self;
                setTimeout(function () {
                    that.annotator.reload()
                }, 300);
            }
        });

        this.props.contractApp.on("searchresults:ready", function () {
            if (self.annotator) {
                var that = self;
                setTimeout(function () {
                    that.annotator.reload()
                }, 300);
            }
        });
        this.props.contractApp.on("searchresults:close", function () {
            if (self.annotator) {
                var that = self;
                setTimeout(function () {
                    that.annotator.reload()
                }, 300);
            }
        });

        this.props.pagesCollection.on("reset", function () {
            self.message = "";
            if (self.props.pagesCollection.models.length === 0) {
                var email = 'info@openlandcontracts.org';
                var link = '<a href="mailto:'+email+'">'+email+'</a>';
                var message = LANG.error_loading_file;
                message = message.replace(':link',link);
                self.message = <div className="no-contract-error">{message}</div>;
            }
            self.forceUpdate();
            self.loadAnnotations();
            self.props.contractApp.triggerScrollToTextPage();
        });
        this.props.contractApp.on("scroll-to-text-page", function () {
            self.scrollToPage(self.props.contractApp.getCurrentPage());
        });
    },
    render: function () {
        var self = this;
        var show_pdf_text = (this.props.metadata) ? this.props.metadata.get('show_pdf_text') : undefined;
         var warning_text_helper = LANG.ocr_text_helper+'<a href={app_url + "/faqs"}>'+LANG.learn_more+'</a>';
        var warningText = (this.message) ? "" : (<div className="text-viewer-warning">
            <span className="pull-right link close" onClick={this.handleClickWarning}>x</span>
            {warning_text_helper}
        </div>);

        var pagesView = (this.message) ? this.message : LANG.loading;

        if (this.props.pagesCollection.models.length > 0) {
            pagesView = [];
            for (var i = 0; i < this.props.pagesCollection.models.length; i++) {
                var model = this.props.pagesCollection.models[i];
                pagesView.push(<TextPageView
                    key={i}
                    contractApp={self.props.contractApp}
                    page={model}/>
                );
            }
        }

        if (typeof show_pdf_text === 'undefined') {
            warningText = '';
        }

        if (show_pdf_text == 0) {
            warningText = (<div className="text-viewer-warning">{LANG.warning_text}</div>);
            pagesView = "";
        }

        return (
            <div className="text-panel" style={this.props.style}>
                {warningText}
                <div className="text-annotator">
                    <div></div>
                    <div className="text-viewer">
                        {pagesView}
                    </div>
                </div>
            </div>
        );
    }

});