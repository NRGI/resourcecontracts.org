var PaginationView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.eventsPipe = options.eventsPipe;
        this.eventsPipe.on("page-change", this.pageChange, this);
        var self = this;
        this.pagination = $(this.el).pagination({
            pages: options.totalPages,
            displayedPages: 5,
            cssStyle: 'light-theme',
            onPageClick: function(pageNumber, event) {
                self.eventsPipe.trigger("page-load", pageNumber);
                self.eventsPipe.trigger("page-scroll", pageNumber);
            }
        });
        return this;
    },
    pageChange: function(pageNumber) {
        this.eventsPipe.trigger("page-load", pageNumber);
        this.pagination.pagination('drawPage', pageNumber);
        this.eventsPipe.trigger("page-scroll", pageNumber);
    }
});