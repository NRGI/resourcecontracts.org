var ViewerPage = Backbone.Model.extend({

});

var ViewerPageCollection = Backbone.Collection.extend({
    model: ViewerPage,
    sort_key: 'page_no',
    comparator: function(item) {
        return item.get(this.sort_key);
    },    
    parse: function(response) {
        return response.result;
    }
});