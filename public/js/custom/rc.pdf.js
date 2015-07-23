var PdfView = Backbone.View.extend({
    initialize: function(options) {
        PDFJS.workerSrc = app_url + '/js/lib/pdfjs/pdf.worker.js';
        this.options = options;
        this.listenTo(this.model, "change:pdf_url", this.render);
        return this;
    },
    render: function() {
        var self = this;
        PDFJS.getDocument(this.model.get('pdf_url')).then(function(pdf) {
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