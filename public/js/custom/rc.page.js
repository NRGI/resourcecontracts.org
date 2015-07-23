var Page = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
        this.eventsPipe = options.eventsPipe;
        this.eventsPipe.on("page-load", this.load, this);
        this.eventsPipe.on("searchresults-hightlight", this.setSearchTerm, this);
        this.set('isReadOnly', !this.options.contractModel.canEdit());
        this.set('isAnnotable', this.options.contractModel.canAnnotate());
        this.set('pageNumber', this.options.pageNumber);
        return this;
    },
    defaults: function() {
        return {
            id: 0,
            pageNumber: 1,
            text: 'Loading!',
        }
    },
    load: function(page) {
        this.set("pageNumber", page);
        var self = this;
        $.ajax({
            url: self.options.loadUrl,
            data: 'page=' + page,
            type: 'GET',
            async: true,
            success: function(response) {
                self.set('id', response.id);
                self.set('text', response.message);
                self.set('pdf_url', response.pdf);
                if (self.searchTerm) {
                    self.highLightText(self.searchTerm);
                }
            }
        });
        return this;
    },
    save: function(htmlContent) {
        $.ajax({
            url: this.options.saveUrl,
            data: {'text': this.get('text'), 'page': this.get('pageNumber')},
            type: 'POST'
        }).done(function (response) {
            this.textUpdated = false;
            $('#message').html('<div class="alert alert-success">'+response.message+'</div>');
            $('html,body').animate({ scrollTop: $('body').offset().top},'slow');
        });
    },    
    setNextPage: function() {
        this.set("pageNumber", 1 + parseInt(this.get('pageNumber')));
        this.load(this.get("pageNumber"));
        this.eventsPipe.trigger('page-scroll-pagination')
    },
    setSearchTerm: function(searchTerm) {
        this.searchTerm = searchTerm;
    },
    highLightText: function(searchTerm) {
        var regex = new RegExp(searchTerm, "gi");
        this.set('text', this.get('text').replace(regex, function(matched) {
            return "<span style='background-color: rgba(80,80,80,0.5);'>" + matched + "</span>";
        }));
    }
});