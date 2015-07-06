var SearchResult = Backbone.Model.extend({});

var SearchResultCollection = Backbone.Collection.extend({
    model: SearchResult,
    initialize: function() {
        // this.reset();
    },
    getSearchTerm: function() {
        return this.searchTerm;
    },
    fetch: function(options, callback) {
        this.searchTerm = options.searchTerm;
        var that = this;            
        $.ajax({
            url : options.url,
            postType : 'JSON',
            type : "POST",
            data : {'q': options.searchTerm}
        }).done(function(response){    
            that.destroy();         
            $.each(response, function(index, result) {
                that.add({text: result.text, pageNumber: result.page_no});
            });
            that.trigger('dataCollected');
        });
    },
    destroy: function() {
        var that = this;
        this.forEach(function(model) {
            that.remove(model);
        });        
    }
});

var SearchResultView = Backbone.View.extend({
    tagName: 'p',
    initialize: function(options) {
        this.options = options;
        return this;
    },
    events: {
        "click a":"changePage"
    },
    render: function() {
        // <li><span><a onclick='annotationClicked(this,"+contract.id+","+annotation.page+")' href='#'>{0}</a> [Page {1}]</span><br><p>{2}</p></li>
        this.$el.html('<a href="#">'+this.model.get('text')+'</a>[Page '+this.model.get('pageNumber')+']');
        return this;
    },
    changePage: function() {
        this.options.pageModel.setSearchTerm(this.options.searchTerm);
        this.options.pageModel.setPageNumber(this.model.get('pageNumber'));
    }

});
var SearchResultListView = Backbone.View.extend({
    tagName: 'div',
    className: 'results',
    events: {
        'click .search-cancel': "close"
    },
    initialize: function(options) {
        this.options = options;
        this.listenTo(this.collection, 'dataCollected', this.dataCollected);
        this.bind('dataCollected', this.dataCollected, this);        
        return this;
    },
    dataCollected: function() {
        this.render();
    },  
    render: function() {
        var that = this;
        this.$el.show();
        this.$el.html('');
        $('.right-document-wrap canvas').hide();
        // this.remove();
        that.$el.append("<a href='#' class='pull-right search-cancel'><i class='glyphicon glyphicon-remove'></i></a>");
        if(this.collection.length) {
            that.$el.append("<p>Total "+this.collection.length+" result(s) found.</p>");
            this.options.collection.each(function(searchResult) {            
                var searchResultView = new SearchResultView({ model: searchResult, pageModel: that.options.pageModel, searchTerm: that.collection.getSearchTerm()});
                that.$el.append(searchResultView.render().$el);
            });
        }
        else {
            that.$el.append('<h4>Result not found</h4>');
        }
        return this;
    },
    close: function() {
        this.$el.hide();
        this.$el.html('');
        $('.right-document-wrap canvas').show();
    }
});

var SearchFormView = Backbone.View.extend({
    events: {
        "click input[type=submit]": "doSearch"
    },
    initialize: function(options) {
        this.template = _.template($("#searchFormTemplate").html(), {} );
        this.bind('doSearch', this.doSearch, this);     
    },
    render: function() {
        this.$el.html(this.template);
        return this;
    },
    doSearch: function(e) {
        e.preventDefault();
        this.collection.destroy();
        this.collection.fetch({"url": this.$('form').attr('action'), "searchTerm": this.$('#textfield').val()});
    }
});