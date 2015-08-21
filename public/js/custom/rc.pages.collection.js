var ViewerPage = Backbone.Model.extend({

});
var ViewerCurrentPage = Backbone.Model.extend({
    default: {
        page: 1
    },
    initialize: function(options) {
        this.collection = options.collection;
    },
    setPage: function(page) {
        if(page <= this.collection.length && page >= 1) {
            this.set({'page': parseInt(page) || this.get('page')});
        }
    },
    getPage: function() {
        return this.get('page') || 1;
    },
    next: function() {
        if(this.getPage() < this.collection.length) {
            this.setPage(this.getPage() + 1);
        }
    },
    previous: function() {
        if(this.getPage() >= 1) {
            this.setPage(this.getPage() - 1);
        }
    }
});
var ViewerPagesCollection = Backbone.Collection.extend({
    model: ViewerPage,
    totalPages: 0,
    firstPage: 1,
    sort_key: 'page_no',
    comparator: function(item) {
        return item.get(this.sort_key);
    },
    initialize: function(models, options) {
        this.url = options.url;
        this.currentPage = options.currentPage || this.currentPage;
    },
    parse: function(response) {
        return response.result;
    }
});
