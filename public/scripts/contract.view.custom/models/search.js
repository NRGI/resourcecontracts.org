var SearchResultRow = Backbone.Model.extend({});
var SearchResultsCollection = Backbone.Collection.extend({
    model: SearchResultRow,
    sort_key: "page_no",
    comparator: function(item) {
        return item.get(this.sort_key);
    },      
    getSearchTerm: function() {
        return this.searchTerm;
    },
    fetch: function(options, callback) {
        this.searchTerm = decodeURI(options.searchTerm);
        this.searchCompleted = false;
        var self = this;
        $.ajax({
            url: this.url,
            dataType : 'json',
            // type: "GET",
            type: "POST",
            data: {
                'q': self.searchTerm
            },
            async: true,
        }).done(function(response) {
            self.destroy();
            $.each(response.results, function(index, result) {
                self.add({
                    text: result.text,
                    page_no: result.page_no
                });
            });
            self.searchCompleted = true;
            self.trigger('reset');
        });
    },
    destroy: function() {
        var self = this;
        _.each(_.clone(self.models), function(model) {
            model.destroy();
        });
    }
});