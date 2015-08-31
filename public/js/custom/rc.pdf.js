var PdfPage = Backbone.Model.extend({
    default: {
        url: '',
        page: ''
    },
    initialize: function() {
        this.bind('add', this.modelAdd, this);
    },
    modelAdd: function(model) {
        console.log(model);
    },
    getUrl: function() {
        return this.get('pdf_url');
    }
});
var PdfPagesCollection = Backbone.Collection.extend({
    model: PdfPage,
    parse: function(response) {
        return response.result;
    }        
});
var PdfCurrentPage = Backbone.Model.extend({
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

var PdfView = Backbone.View.extend({
    initialize: function(options) {
        this.currentPage = options.currentPage;
        this.currentPage.on('page-change', this.render, this);
        this.pdf_url = options.pdf_url;
        PDFJS.workerSrc = app_url + '/js/lib/pdfjs/pdf.worker.js';
        this.options = options;
        // this.listenTo(this.model, "change:pdf_url", this.render);
        return this;
    },
    render: function() {
        var self = this;
        var pdf_url = "";
        if(this.collection) {
            pdf_url = this.collection.findWhere({page_no: this.currentPage.getPage()}).getUrl();
        } else {
            pdf_url = this.currentPage.getUrl();
        }
        PDFJS.getDocument(pdf_url).then(function(pdf) {
            // Using promise to fetch the page
            pdf.getPage(1).then(function(page) {
                var scale = 1;
                var viewport = page.getViewport(scale);
                // Prepare canvas using PDF page dimensions
                var canvas = document.getElementById(self.options.el);
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width - 20;
                // Render PDF page into canvas context
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        });
        return this;
    }
});

var PdfViewerPagination = Backbone.View.extend({
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
        $("#goto_pdfpage").val(this.currentPage.getPage());
    },
    render: function() {
        this.changePage();
    },
    nextPage: function(e) {        
        e.preventDefault();
        var oldPage = this.currentPage.getPage();
        this.currentPage.next();
        var newPage = this.currentPage.getPage();
        if(oldPage !== newPage) {
            this.currentPage.trigger('page-change', this.currentPage.getPage());
        }
    },
    previousPage: function(e) {
        e.preventDefault();
        this.currentPage.previous();
        this.currentPage.trigger('page-change', this.currentPage.getPage());
    }
});