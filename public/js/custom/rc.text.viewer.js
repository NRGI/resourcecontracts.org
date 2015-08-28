function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
var TextPageView = Backbone.View.extend({
    events: {
        "mousedown": "mousedown"
    },
    initialize: function(options) {
        this.parent = options.parent;
        this.currentPage = options.currentPage;
    },
    mousedown: function(e) {
        this.currentPage.setPage(this.model.get('page_no'));
    },
    render: function() {
        var template = _.template($('#text-page-partial-view').html());
        this.setElement(template(this.model.toJSON()));
        var self = this;
        $(this.el).waypoint({
            context: document.getElementById('text-viewer-wrapper-overflow-scroll'),
            handler: function(direction) {                
                if(direction == "up" && self.parent.scrolled) {
                    self.currentPage.setPage(self.model.get('page_no')-1);
                }
            },
            offset: "50%"
        });
        $(this.el).waypoint({
            context: document.getElementById('text-viewer-wrapper-overflow-scroll'),
            handler: function(direction) {
                if(direction == "down" && self.parent.scrolled) {
                    self.currentPage.setPage(self.model.get('page_no'));
                } 
            },
            offset: "80%"
        });
        return this;
    },
});
var TextViewerView = Backbone.View.extend({
    initialize: function(options) {
        this.currentPage = options.currentPage;
        this.rcEvents = options.rcEvents;
        this.rcEvents.on('page-change', this.gotoPage, this);
        this.rcEvents.on('scroll-to-annotation', this.scrollToAnnotation, this);
        this.currentPage.on('scroll-to-page', this.gotoPage, this);
        this.collection.on('reset', this.render, this);
        $(this.el).one('scroll', $.proxy(this.scroll, this));
        this.textEl = $(this.el).find('div#text-viewer-overflow-scroll')[0];
        this.scrolled = false;
    },
    scroll: function(e) {
        //to make sure that waypoint handler is called once scrolled
        this.scrolled = true;
    },
    render: function() {
        var self = this;
        this.collection.sort();
        $(self.textEl).html('');
        _.forEach(_.clone(this.collection.models), function(model) {
            $(self.textEl).append(new TextPageView({model: model, parent: self, currentPage: self.currentPage}).render().$el);
        });
    },
    gotoPage: function(page) {
        var page = page || this.currentPage.getPage();
        $(this.el).animate(
            {scrollTop: $(this.el).scrollTop() + ($('#'+page).offset().top - $(this.el).offset().top) - $(this.el).height()/2 + $('#'+page).height()/2
        }, 50);
    },
    scrollToAnnotation: function(annotationStartPos) {
        console.log(annotationStartPos);
        $(this.el).animate(
            {scrollTop: $(this.el).scrollTop() + ($(annotationStartPos).offset().top - $(this.el).offset().top) - $(this.el).height()/2 + $(annotationStartPos).height()/2
        }, 50);
    }
});

var TextViewerPagination = Backbone.View.extend({
    tagName: 'div',
    events: {
        "click .next": "nextPage",
        "click .previous": "previousPage"
    },
    initialize: function(options) {
        this.currentPage = options.currentPage;
        this.currentPage.on('change:page', this.changePage, this);
        this.render();
    },
    changePage: function() {
        $("#goto_page").val(this.currentPage.getPage());
    },
    render: function() {
        this.changePage();
    },
    nextPage: function(e) {
        e.preventDefault();
        this.currentPage.next();
        this.currentPage.trigger('scroll-to-page', this.currentPage.getPage());
    },
    previousPage: function(e) {
        e.preventDefault();
        this.currentPage.previous();
        this.currentPage.trigger('scroll-to-page', this.currentPage.getPage());
    }
});
