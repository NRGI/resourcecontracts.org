var Contract = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
        this.annotationCollection = new MyAnnotationCollection();
        this.searchResultCollection = new SearchResultCollection();
        this.pageModel = new Page({
            id: options.currentPageId,
            pageNumber: options.currentPage || 1,             
            loadUrl: options.textLoadAPI, 
            saveUrl: options.textSaveAPI,
            annotatorjsUrl: options.annotatorjsAPI,
            contractModel: this
        });
        this.pageModel.load();
    },
    getPageModel: function() {
        return this.pageModel;
    },
    getTotalPages: function() {
        return this.options.totalPages;
    },
    canAnnotate: function() {
        return this.options.canAnnotate || false;
    },
    canEdit: function() {
        return this.options.canEdit || false;
    },
    getPaginationEl: function() {
        return this.options.paginationEl;
    },
    getEditorEl: function() {
        return this.options.editorEl
    },
    getPdfviewEl: function() {
        return this.options.pdfviewEl;
    },
    getAnnotatorjsEl: function() {
        return this.options.annotatorjsEl;
    },
    getAnnotationsListEl: function() {
        return this.options.annotationslistEl;
    },
    addAnnotation: function(annotation) {
        this.annotationCollection.add(annotation);
    },
    getAnnotationPullAPI: function() {
        return this.options.annotatationPullAPI;
    }
});

var Page = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
        this.metadata = '';
        this.set('isReadOnly', !this.options.contractModel.canEdit());
        this.set('isAnnotable', this.options.contractModel.canAnnotate());
        this.set('pageNumber', this.options.pageNumber);
        return this;
    },
    defaults: function() {
        return {
            pageNumber: 1,
            text: 'Not loaded yet!',
        }
    },
    setPageNumber: function(pageNumber) {
        this.set('pageNumber', pageNumber);
        this.load();
    },
    setSearchTerm: function(searchTerm) {
        this.searchTerm = searchTerm;
    },
    load: function() {
        var that = this;
        $.ajax({
            url: that.options.loadUrl,
            data: 'page=' + that.get('pageNumber'),
            type: 'GET',
            async: false,
            success: function (response) {
                that.set('text', response.message);
                that.set('pdf_url', response.pdf);
                if(that.searchTerm) {
                    that.highLightText(that.searchTerm);
                }
                that.trigger('pageChange');
            }
        });
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
    showMetadata : function(request_url){
        var self = this;
        if (this.metadata == '') {
            $.ajax({
                url: request_url,
                type: 'GET'
            }).done(function (response) {
                self.metadata = response;
                self.show_metadata(response);
            });
        }
        else {
            this.show_metadata(this.metadata);
        }
    },
    show_metadata: function (metadata) {
        if ($('.popup-metadata').length > 0) {
            $('.popup-metadata').remove();
        }
        else {
            var html = '<div class="popup-metadata">' +
                '<p><strong>Contract Title:</strong> ' + metadata.contract_name + '</p>' +
                '<p><strong>Country:</strong> ' + metadata.country.name + '</p>' +
                '<p><strong>Date of signature:</strong> ' + metadata.signature_date + '</p>' +
                '<p><strong>Resource:</strong> ' + metadata.resource + '</p>' +
                '</div>';
            $('.panel-heading').append(html);
        }
    },
    getPdfLocation: function() {
        return this.get('pdf_url');
       // return "/data/{0}/pages/{1}.pdf".format(this.options.contractModel.get('id'), this.get('pageNumber'));
    },
    highLightText: function(searchTerm) {
        var regex = new RegExp(searchTerm, "gi");        
        this.set('text',this.get('text').replace(regex, function(matched) {
                return "<span style='background-color: rgba(80,80,80,0.5);'>" + matched + "</span>";
            })
        );
    }
});

var PageView = Backbone.View.extend({
    initialize: function(options) {
        this.bind('pageTextLoaded', this.pageTextLoaded);        
        this.options = options;
        this.paginationView = options.paginationView || null;
        this.textEditorView = options.textEditorView || null;
        this.pdfView = options.pdfView || null;
        this.annotatorjsView = options.annotatorjsView || null;
        this.annotationsListView = options.annotationsListView || null;
        this.searchFormView = options.searchFormView || null;
        // this.paginationView = new PaginationView({paginationEl: options.contractModel.getPaginationEl(), totalPages: options.contractModel.get('totalPages'), pageModel: options.model});
        // this.textEditorView = new TextEditorView({editorEl: options.contractModel.getEditorEl(), model: options.model});
        // this.annotationList = new AnnotationSideList({el: options.contractModel.getAnnotationListEl(), collection: options.contractModel.annotationCollection, pageModel: options.model}).render();
        // this.listenTo(this.model, 'pageChange', this.pageChange);
        // this.bind('pageChange', this.pageChange);
        return this;
    },
    render: function() {
        if(this.paginationView) this.paginationView.render();
        if(this.textEditorView) this.textEditorView.render();
        if(this.pdfView) this.pdfView.render();
        if(this.annotatorjsView) this.annotatorjsView.render();
        // if(this.annotationsListView) this.annotationsListView.render();
        if(this.searchFormView) this.searchFormView.render();        
        return this;
    },
    toggleAnnotationList: function() {
        this.annotationsListView.toggle();
    },
    saveClicked: function() {
        this.options.pageModel.save();
    },
    showMetadata:function(req){
        this.options.pageModel.showMetadata(req);
    },
    pageChange: function() {
        // this.paginationView.setPage(this.options.pageModel.get('pageNumber'));
    },
    pageTextLoaded: function() {
        // console.log('loaded');
    }
});

var TextEditorView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;        
        if(this.options.pageModel.get('isReadOnly')) {
            $('#saveButton').hide();
            $('#toolbar').hide();
        }
        else $('#saveButton').show();
        this.editor = new Quill(this.options.editorEl, {readOnly: this.options.pageModel.get('isReadOnly')});
        this.listenTo(this.options.pageModel, 'pageChange', this.render);
        this.bind('pageChange', this.render);
        var that = this;
        this.editor.on('text-change', function(delta, source) {
              if (source == 'api') {
                //none
              } else if (source == 'user') {
                    that.options.pageModel.set('text', that.editor.getHTML());
              }            
        })
        return this;
    },
    render: function() {
        this.editor.setHTML(this.options.pageModel.get('text'));
        this.trigger('pageTextLoaded');
        return this;
    },
    getHtmlText: function() {
        return this.editor.getHTML();
    }
});

var PdfView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.listenTo(this.options.pageModel, 'pageChange', this.render);
        this.bind('pageChange', this.render);
        return this;
    },
    render: function() {
        var that = this;
        PDFJS.workerSrc = '/js/lib/pdfjs/pdf.worker.js';
        PDFJS.getDocument(this.options.pageModel.getPdfLocation()).then(function (pdf) {
            // Using promise to fetch the page
            pdf.getPage(1).then(function (page) {
                var scale = 1;
                var viewport = page.getViewport(scale);
                // Prepare canvas using PDF page dimensions
                var canvas = document.getElementById(that.options.pdfviewEl);
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

var PaginationView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.listenTo(this.options.pageModel, 'pageChange', this.setPage);
        this.bind('pageChange', this.render);

        this.pagination = $(options.paginationEl).pagination({
            pages: options.totalPages,
            displayedPages: 5,
            cssStyle: 'light-theme',
            onPageClick: function (pageNumber, event) {
                options.pageModel.setPageNumber(pageNumber);
            }
        });
        return this;
    },
    render: function() {
        return this;
    },
    setPage: function() {
        // console.log("pagination-view setpage:",this.options.pageModel.get('pageNumber'));
        this.pagination.pagination('drawPage', this.options.pageModel.get('pageNumber'))
        return this;
    }
});




