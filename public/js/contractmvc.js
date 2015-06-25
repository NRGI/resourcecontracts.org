var Contract = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
        this.annotationCollection = new MyAnnotationCollection();
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
    }
});

var Page = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
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
    load: function() {
        var that = this;
        $.ajax({
            url: that.options.loadUrl,
            data: 'page=' + that.get('pageNumber'),
            type: 'GET',
            async: false,
            success: function (response) {
                that.set('text', response.message);
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
            $('#message').html('<div class="alert alert-success">Saved</div>');
            $('html,body').animate({ scrollTop: $('body').offset().top},'slow');
        });
    },
    getPdfLocation: function() {
        return "/data/{0}/pages/{1}.pdf".format(this.options.contractModel.get('id'), this.get('pageNumber'));
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
        if(this.annotationsListView) this.annotationsListView.render();        
        return this;
    },
    toggleAnnotationList: function() {
        this.annotationsListView.toggle();
    },
    saveClicked: function() {
        this.options.pageModel.save();
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
        if(this.options.pageModel.get('isReadOnly')) $('#saveButton').hide();
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
                canvas.width = viewport.width;
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

var AnnotatorjsView = Backbone.View.extend({
    initialize: function(options) {
        console.log(options);
        this.options = options;
        this.listenTo(this.options.pageModel, 'pageChange', this.pageUpdated);
        this.bind('pageChange', this.pageUpdated);
        this.content = $(this.options.annotatorjsEl).annotator({readOnly: !this.options.pageModel.get('isAnnotable')});
        this.content.annotator('addPlugin', 'Tags');
        this.availableTags = this.options.tags;
        return this;
    },
    pageUpdated: function() {
        // console.log('pageUpdated');
        var that = this;
        var store = this.content.data('annotator').plugins.Store;
        if(store.annotations) store.annotations = [];

        store.options.loadFromSearch = { 
            'url': that.options.contractModel.get('annotatorjsAPI'),
            'contract': that.options.contractModel.get('id'),
            'document_page_no': that.options.pageModel.get('pageNumber')
        };            
        store.options.annotationData = { 
            'url': that.options.contractModel.options.annotatorjsAPI,
            'contract': that.options.contractModel.get('id'),
            'document_page_no': that.options.pageModel.get('pageNumber'),
            'page_id': that.options.pageModel.get('id')
        }
        store.loadAnnotationsFromSearch(store.options.loadFromSearch)                

    },
    render: function() {
        var that = this;
        console.log("ajs", that.options.pageModel.get('pageNumber'));
        var page = that.options.pageModel.get('pageNumber');
        if(this.content.data('annotator').plugins.Store) {
            var store = this.content.data('annotator').plugins.Store;
            if(store.annotations) store.annotations = [];
            store.options.loadFromSearch = { 
                'url': that.options.contractModel.get('annotatorjsAPI'),
                'contract': that.options.contractModel.get('id'),
                'document_page_no': that.options.pageModel.get('pageNumber')
            };            
            store.loadAnnotationsFromSearch(store.options.loadFromSearch)                
        } else {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                loadFromSearch: {
                    'url': that.options.contractModel.get('annotatorjsAPI'),
                    'contract': that.options.contractModel.get('id'),
                    'document_page_no': that.options.pageModel.get('pageNumber')
                },
                annotationData: {
                    'url': that.options.contractModel.get('annotatorjsAPI'),
                    'contract': that.options.contractModel.get('id'),
                    'document_page_no': that.options.pageModel.get('pageNumber'),
                    'page_id': that.options.pageModel.get('id')
                }
            }); 
        }
        return this;
    }                   
});

var MyAnnotation = Backbone.Model.extend();

var MyAnnotationCollection = Backbone.Collection.extend({
    model: MyAnnotation
})

var AnnotationSideView = Backbone.View.extend({
    tagName: 'div',
    initialize: function(options) {
        this.options = options;
    },
    events: {
        "click a":"changePage"
    },
    render: function() {
        // <li><span><a onclick='annotationClicked(this,"+contract.id+","+annotation.page+")' href='#'>{0}</a> [Page {1}]</span><br><p>{2}</p></li>
        this.$el.html('<a href="#">'+this.model.get('quote')+'</a>[Page '+this.model.get('page')+']<br><p>'+this.model.get('text')+'</p>');
        return this;
    },
    changePage: function() {
        this.options.pageModel.setPageNumber(this.model.get('page'));
    }
});

var AnnotationsListView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.$el = $(options.annotationslistEl);
    },
    render: function() {
        var that = this;
        console.log(this.options);
        // that.$el.append('<ul>');
        this.collection.each(function(annotation) {
            var annotationSideView = new AnnotationSideView({ model: annotation, pageModel: that.options.pageModel});
            that.$el.append(annotationSideView.render().$el);
        })
        // that.$el.append('</ul>');
        return this;
    },
    toggle: function() {
        this.$el.toggle();
    }
});
