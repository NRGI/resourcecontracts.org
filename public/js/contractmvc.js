var Contract = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
        this.annotationCollection = new MyAnnotationCollection();
        this.pageModel = new Page({pageNumber: 1, url: options.textLoadAPI});
    },
    getEditorEl: function() {
        return '#editor_' + this.options.position;
    },
    getPaginationEl: function() {
        return '#pagination_' + this.options.position;
    },
    getAnnotationListEl: function() {
        return '#annotations_' + this.options.position;
    },
    addAnnotation: function(annotation) {
        this.annotationCollection.add(annotation);
    },
    loadPageView: function() {
        this.pageView = new PageView({model: this.pageModel, contractModel: this});
        this.pageModel.load();        
    },
    getPageView: function() {
        return this.pageView;
    }

});

var Page = Backbone.Model.extend({
    initialize: function(options) {
        this.set('pageNumber', options.pageNumber);
        return this;
    },
    defaults: function() {
        return {
            pageNumber: 1,
            isReadOnly: true,
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
            url: this.get('url'),
            data: 'page=' + this.get('pageNumber'),
            type: 'GET',
            async: false,
            success: function (response) {
                that.set('text', response.message);
                that.trigger('pageChange');
            }
        });
    }
});

var PageView = Backbone.View.extend({
    initialize: function(options) {
        this.paginationView = new PaginationView({paginationEl: options.contractModel.getPaginationEl(), totalPages: options.contractModel.get('totalPages'), pageModel: options.model});
        this.textEditorView = new TextEditorView({editorEl: options.contractModel.getEditorEl(), model: options.model});
        this.annotationList = new AnnotationSideList({el: options.contractModel.getAnnotationListEl(), collection: options.contractModel.annotationCollection, pageModel: options.model}).render();
        this.listenTo(this.model, 'pageChange', this.pageChange);
        this.bind('pageChange', this.pageChange);
    },
    render: function() {
        this.paginationView.render();
        this.textEditorView.render();
    },
    toggleAnnotationList: function() {
        this.annotationList.toggle();
    },
    pageChange: function() {
        this.paginationView.setPage(this.model.get('pageNumber'));
    }
});

var TextEditorView = Backbone.View.extend({
    initialize: function(options) {
        this.editor = new Quill(options.editorEl, {readOnly: this.model.get('isReadOnly')});
        this.listenTo(this.model, 'pageChange', this.render);
        this.bind('pageChange', this.render);
        return this;
    },
    render: function() {
        this.editor.setHTML(this.model.get('text'));
        return this;
    }
});

var PaginationView = Backbone.View.extend({
    initialize: function(options) {
        this.pagination = $(options.paginationEl).pagination({
            pages: options.totalPages,
            displayedPages: 5,
            cssStyle: 'light-theme',
            onPageClick: function (pageNumber, event) {
                options.pageModel.setPageNumber(pageNumber);
            }
        });
    },
    render: function() {
        return this;
    },
    setPage: function(page) {
        this.pagination.pagination('drawPage', page)
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

var AnnotationSideList = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.$el = $(options.el);
    },
    render: function() {
        var that = this;
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

var AnnotatorjsView = Backbone.View.extend({
    initialize: function(options) {
        this.content = $(options.annotatorjsEl).annotator({readOnly: true});//options.contractModel.get('canAnnotate')});
        this.content.annotator('addPlugin', 'Tags');
        this.availableTags = options.tags;
        return this;
    },
    render: function(page) {
        if(this.content.data('annotator').plugins.Store) {
            var store = this.content.data('annotator').plugins.Store;
            if(store.annotations) store.annotations = [];
            store.options.loadFromSearch = {'url': options.api,'contract': options.contractId,'document_page_no': page};
            store.loadAnnotationsFromSearch(store.options.loadFromSearch)                
        } else {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                loadFromSearch: {
                    'url': options.api,
                    'contract': options.contractId,
                    'document_page_no': page
                }
            }); 
        }
    }                   
});