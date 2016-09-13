var PdfPage = Backbone.Model.extend({
    defaults: {
        content: ""
    },
    initialize: function(options) {
        var self = this;
        self.init = false;
        this.contractApp = options.contractApp;
        this.contractApp.on("change:pdf_url", function() {
            self.loadPdf();
        });
        this.contractApp.on("change:page_no", function() {
            if (self.contractApp.getView() === "pdf") {
                debug("pdf.js change:page_no called");
                //load pdf if only in pdfview
                self.loadPdf();
            }
        });
    },
    fetchBlob: function(uri, callback) {
        if (this.xhr && this.xhr.readystate != 4) {
            //if the users clicks pagination quickly, abort previous ajax calls.
            this.xhr.abort();
        }
        this.xhr = new XMLHttpRequest();
        this.xhr.open('GET', uri, true);
        this.xhr.responseType = 'blob';
        this.xhr.onload = function(e) {
            if (this.status == 200) {
                var blob = new Blob([this.response], {
                    type: 'application/pdf'
                });
                var url = URL.createObjectURL(blob);
                if (callback) {
                    callback(url);
                }
            }
        }
        this.xhr.send();
    },
    loadPdf: function() {
        var self = this;
        if (this.contractApp.getPdfUrl().trim() !== "") {
            self.init = true;
            debug('setting content to -');
            self.set({
                content: "-"
            });
            self.trigger("change:content");
            this.fetchBlob(this.contractApp.getPdfUrl(), function(blob) {
                self.init = true;
                debug("pdf.js loadPdf: fetched ", this.contractApp.getPdfUrl(), " setting pdfpage: content");
                self.set({
                    content: blob
                });
            });
        } else {
            debug("pdf.js loadPdf: no url defined for pdf "," setting pdfpage: content to false");
            self.set({
                content: "-1"
            });
        }
    },
});