var TextSearchForm = React.createClass({
  handleSubmit: function(e) {
    e.preventDefault();
    var searchQuery = React.findDOMNode(this.refs.searchInput).value.trim();
    if(!searchQuery) {
      return;
    }
    document.location.hash = '#/search/' + encodeURI(searchQuery);
  },
  render: function() {
    return (
      <div className="text-search">
      <form onSubmit={this.handleSubmit}>
        <input type="text" className="" ref="searchInput" placeholder="Search in this document" />
      </form>
      </div>
    );
  }
});
var TextSearchResultRow = React.createClass({
  handleClick: function() {
    this.props.contractApp.setCurrentPage(this.props.resultRow.get("page_no"));
    this.props.contractApp.triggerScrollToTextPage();
    // this.props.currentPage.set({"page_no": this.props.resultRow.get("page_no")});
    // this.props.currentPage.trigger("scroll-to-page");
  },
  highlightSearchQuery: function(text, highlightword) {
    highlightword = decodeURI(highlightword);
    var re = new RegExp(highlightword, "gi");
    return text.replace(re,"<span class='search-highlight-word'>" + highlightword + "</span>");
  },  
  render: function() {
    var text = this.highlightSearchQuery(this.props.resultRow.get("text"), this.props.contractApp.getSearchQuery());
    text = "Pg " + this.props.resultRow.get("page_no") + "&nbsp;" + text;
    return(
      <div className="search-result-row link" onClick={this.handleClick}>
        <span dangerouslySetInnerHTML={{__html: text}} />
      </div>
    );
  }
});
var TextSearchResultsList = React.createClass({
  componentDidMount: function() {
    var self = this;
    this.props.searchResultsCollection.on("reset", function() {
      self.forceUpdate();
      self.props.contractApp.trigger("searchresults:ready");
    });
  },
  handleCloseSearchResults: function() {
    this.props.contractApp.trigger("searchresults:close");
    document.location.hash = '#/text';
    this.props.contractApp.setView("text");
  },
  render: function() {
    var self = this;
    var resultsView = "searching ...";
    if(this.props.searchResultsCollection.models.length > 0) {
      resultsView = this.props.searchResultsCollection.models.map(function(model, i) {
        return (
          <TextSearchResultRow
            key={i}
            contractApp={self.props.contractApp} 
            resultRow={model} />
        );
      });
    } 
    else if(this.props.searchResultsCollection.searchCompleted === true || this.props.searchResultsCollection.length == 0) {
      resultsView = "No results found";
    }

    return (
      <div style={this.props.style} className="search-results-list">
      <span className="pull-right link close" onClick={this.handleCloseSearchResults}>x</span>
        {resultsView}
      </div>
    );
  }
});