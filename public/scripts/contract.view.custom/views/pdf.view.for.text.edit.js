var PdfPaginationView = React.createClass({
  getInitialState: function() {
    return {
      visiblePage: 1,
      totalPages: 0
    }
  },
  changePage: function(page_no) {
    this.refs.userInput.getDOMNode().value = page_no;
    this.setState({visiblePage: page_no});
    this.props.contractApp.setCurrentPage(page_no);
  },
  clickPrevious: function(e) {
    e.preventDefault();
    if(this.state.visiblePage > 1) {
      this.changePage(this.state.visiblePage-1);
    }
  },
  clickNext: function(e) {
    e.preventDefault();
    if(this.state.visiblePage < this.state.totalPages) {
      this.changePage(this.state.visiblePage+1);
    }
  },
  handleKeyDown: function(e) {
    if(e.keyCode == 13) {
      var inputPage = parseInt(this.refs.userInput.getDOMNode().value);
      if(inputPage > 0 && inputPage <= this.state.totalPages) {
        this.changePage(inputPage);
      } else {
        this.changePage(this.state.visiblePage);
      }     
    }
  },
  componentDidMount: function() {
    var self = this;
    self.setState({totalPages: self.props.contractApp.getTotalPages()});
    this.props.contractApp.on("update-pdf-pagination-page", function(page_no) {
      self.refs.userInput.getDOMNode().value = page_no;
      self.setState({visiblePage: page_no});
    });
    this.refs.userInput.getDOMNode().value = this.state.visiblePage;
  },
  render: function() {
    return (
      <div className="pdf-pagination pagination" style={this.props.style}>
        <a href="#" className="previous" onClick={this.clickPrevious}>{LANG.previous}</a>
        <input type="text" className="goto" ref="userInput" onKeyDown={this.handleKeyDown} />
        <a href="#" className="next" onClick={this.clickNext}>{LANG.next}</a> {LANG.of} {this.state.totalPages}
      </div>
    );
  }
});


var PdfZoom = React.createClass({
  getInitialState: function () {
    return {
      scale: 1
    }
  },
  handleClick: function (e, ev) {
    var type = e.target.getAttribute('data-ref');
    var int = this.state.scale;

    if (int < 2 && type == 'increase') {
      int = int + 0.25;
    }

    if (int > 0.5 && type == 'decrease') {
      int = int - 0.25;
    }
    this.setState({scale: int});
    this.props.contractApp.setPdfScale(int);
  },
  render: function () {
    var selectedClass = "scale-" + this.state.scale;
    $('.pdf-zoom-options span').removeClass('scale-selected');
    $('.pdf-zoom-options .' + selectedClass).addClass('scale-selected');
    var zoom = this.state.scale * 100;
    return (
        <div>
          <div className="pdf-zoom-options" style={this.props.style}>
            <span>{LANG.zoom}</span>
            <a className="btn btn-default" data-ref="decrease" href="#" onClick={this.handleClick}>-</a>
            <p>{zoom}%</p>
            <a className="btn btn-default" data-ref="increase" href="#" onClick={this.handleClick}>+</a>
          </div>
        </div>
    );
  }
});

var PdfViewer = React.createClass({
  componentDidMount: function() {
    var self = this;
    this.props.contractApp.on("change:pdfscale", function() {
      self.forceUpdate();
    });
  },
  render: function() {
      var page_no = this.props.contractApp.getCurrentPage();
      var pdfUrl = this.props.contractApp.getPdfUrl();
      return (
        <div className="pdf-viewer pdf-annotator" style={this.props.style}>
        <Pdf
          content={this.props.pdfPage.get("content")}
          contractApp={this.props.contractApp}
          pdfPage={this.props.pdfPage}
          page={1}
          scale={parseFloat(this.props.contractApp.getPdfScale())||1}
          onPageRendered={this._onPageRendered} />
        </div>
      );
  }
});
