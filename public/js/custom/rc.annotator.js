var AnnotatorjsView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.api = options.api,
        this.listenTo(this.model, 'change:text', this.pageUpdated);
        this.content = $(this.options.el).annotator({readOnly: !this.model.get('isAnnotable')});
        this.content.annotator('addPlugin', 'MyTags');
        this.content.annotator('addPlugin', 'AnnotatorEvents');
        this.content.annotator('addPlugin', 'AnnotoriousImagePlugin');
        this.content.data('annotator').plugins.MyTags.availableTags = options.availableTags
        this.content.data('annotator').plugins.AnnotatorEvents.collection = options.collection;
        this.content.data('annotator').plugins.AnnotatorEvents.pageModel = this.model;
        // this.content.data('annotator').plugins.AnnotatorEvents.page = options.collection;
        this.annotationCategories = options.annotationCategories;
        this.populateCategories();
        return this;
    },
    populateCategories: function() {
        this.content.annotator('addPlugin', 'Categories', {
            category: this.annotationCategories.pluck('name')
        });
    },    
    pageUpdated: function() {
        var that = this;
        var store = this.content.data('annotator').plugins.Store;
        if(store.annotations) store.annotations = [];
        store.options.loadFromSearch = { 
            'url': that.api,
            'contract': that.options.contractModel.get('id'),
            'page': that.model.get('pageNumber'),
            'document_page_no': that.model.get('pageNumber')
        };            
        store.options.annotationData = { 
            'url': that.api,
            'contract': that.options.contractModel.get('id'),
            'document_page_no': that.model.get('pageNumber'),
            'page': that.model.get('pageNumber'),
            'page_id': that.model.get('id')
        }
        store.loadAnnotationsFromSearch(store.options.loadFromSearch)
    },
    render: function() {
        var that = this;
        var page = that.model.get('pageNumber');
        if(this.content.data('annotator').plugins.Store) {
            var store = this.content.data('annotator').plugins.Store;
            if(store.annotations) store.annotations = [];
            store.options.loadFromSearch = { 
                'url': that.api,
                'contract': that.options.contractModel.get('id'),
                'page': that.model.get('pageNumber'),
                'document_page_no': that.model.get('pageNumber')
            };            
            store.loadAnnotationsFromSearch(store.options.loadFromSearch)                
        } else {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                loadFromSearch: {
                    'url': that.api,
                    'contract': that.options.contractModel.get('id'),
                    'page': that.model.get('pageNumber'),
                    'document_page_no': that.model.get('pageNumber')                    
                },
                annotationData: {
                    'url': that.api,
                    'contract': that.options.contractModel.get('id'),
                    'page': that.model.get('pageNumber'),
                    'document_page_no': that.model.get('pageNumber'),
                    'page_id': that.model.get('id')
                }
            }); 
        }
        return this;
    }                   
});