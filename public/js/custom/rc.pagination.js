var PaginationView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;

        this.eventsPipe = options.eventsPipe;
        this.gotoPageView = options.gotoPageView;
        this.eventsPipe.on("page-change", this.pageChange, this);
        var self = this;
        this.pagination = $(this.el).pagination({
            pages: options.totalPages,
            displayedPages: 5,
            cssStyle: 'light-theme',
            onPageClick: function(pageNumber, event) {
                self.gotoPageView.setPageNumber(pageNumber);
                self.eventsPipe.trigger("page-load", pageNumber);
                self.eventsPipe.trigger("page-scroll", pageNumber);
            }
        });
        return this;
    },
    pageChange: function(pageNumber) {
        this.gotoPageView.setPageNumber(pageNumber);
        this.eventsPipe.trigger("page-load", pageNumber);
        this.pagination.pagination('drawPage', pageNumber);
        this.eventsPipe.trigger("page-scroll", pageNumber);
    }
});

var GotoPageView = Backbone.View.extend({
    events: {
        "click button": "gotoPage"
    },
    initialize: function(options) {
        this.eventsPipe = options.eventsPipe;
        this.totalPages = options.totalPages;
    },
    setPageNumber:function(page) {
        $("#goto_page").val(page);
    },
    gotoPage: function(e) {
        page = $("#goto_page").val();
        e.preventDefault();
        if($.isNumeric(page) && parseInt(this.totalPages) > parseInt(page)){
            $(".ql-editor").html("loading!")
            this.eventsPipe.trigger("page-change", parseInt(page));
        }else{
            $("#message").html('<div class="alert alert-danger">Enter valid page number.</div>');
        }

    }
});