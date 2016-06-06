var Pdf = React.createClass({
    displayName: 'React-PDF',
    propTypes: {
        file: React.PropTypes.string,
        content: React.PropTypes.string,
        page: React.PropTypes.number,
        scale: React.PropTypes.number,
        onDocumentComplete: React.PropTypes.func,
        onPageComplete: React.PropTypes.func
    },
    getInitialState: function () {
        return {
            message: ""
        };
    },
    loadFile: function () {
        var self = this;
        var content = this.props.pdfPage.get("content");
        var email = 'nrgi@yipl.com.np';
        var link = '<a href="mailto:'+email+'">'+email+'</a>';
        var message = LANG.error_loading_file;
        message = message.replace(':link',link);
        if (content === "-1" || !content) {
            this.setState({
                page: "",
                content: "",
                message: '<div class="no-contract-error">'+message+'</div>'
            });
        } else {
            if (content !== "-") {
                debug("react.pdf.js loadFile: getDocument content called");
                this.setState({
                    message: "",
                    content: content
                });
                PDFJS.getDocument(content).then(this._onDocumentComplete);
            } else {
                this.setState({
                    page: "",
                    message: "",
                    content: ""
                });
            }
        }
    },
    componentDidMount: function () {
        var self = this;
        this.props.pdfPage.on("change:content", function () {
            debug("react.pdf.js pdfPage change:content called");
            self.loadFile();
        });
        this.props.contractApp.on("change:pdfscale", function () {
            debug("react.pdf.js pdfPage change:pdfscale called");
            self.forceUpdate();
            //self.loadFile();
        });
    },
    render: function () {
        var self = this;
        if (!!this.state.page) {
            setTimeout(function () {
                if (self.isMounted()) {
                    var canvas = self.refs.pdfCanvas.getDOMNode();
                    var context = canvas.getContext('2d');
                    var scale = self.props.scale;
                    var viewport = self.state.page.getViewport(1);
                    if (viewport.width > 600 && scale === 1) {
                        scale = 595.0 / viewport.width;
                    } else if (viewport.width > 600) {
                        scale = scale * 595.0 / viewport.width;
                    }
                    viewport = self.state.page.getViewport(scale);
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    var pageRendering = self.state.page.render(renderContext);
                    var completeCallback = pageRendering._internalRenderTask.callback;
                    pageRendering._internalRenderTask.callback = function (error) {
                        //Step 2: what you want to do before calling the complete method
                        debug("react.pdf pageRendering callback", error);
                        completeCallback.call(this, error);
                        //Step 3: do some more stuff
                        if (!!self.props.onPageRendered && typeof self.props.onPageRendered === 'function') {
                            if (!!self.state.content) {
                                self.props.onPageRendered();
                            }
                        }
                    };
                }
            }, 100);
            return (React.createElement("canvas", {ref: "pdfCanvas"}));
        }

        if (this.state.message) {
            debug("react.pdf  showing generic message", this.state.message);
            return (<div dangerouslySetInnerHTML={{__html: this.state.message}} />);
        } else {
            var page_no = this.props.contractApp.getCurrentPage();
            debug("react.pdf showing page loader", page_no);
            this.removeAnnotations();
            return (this.props.loading || React.createElement("div", null, LANG.loading_pdf+' '+ page_no));
        }
    },
    removeAnnotations : function(){
        $('.annotator-viewer').addClass('annotator-hide');
        $('.annotator-pdf-hl').remove();
    },
    _onDocumentComplete: function (pdf) {
        if (!!this.props.onDocumentComplete && typeof this.props.onDocumentComplete === 'function') {
            this.props.onDocumentComplete(pdf.numPages);
        }
        pdf.getPage(parseInt(this.props.page)).then(this._onPageComplete);
    },
    _onPageComplete: function (page) {
        this.setState({page: page});
        if (!!this.props.onPageComplete && typeof this.props.onPageComplete === 'function') {
            this.props.onPageComplete(page.pageIndex + 1);
        }
    }
});
