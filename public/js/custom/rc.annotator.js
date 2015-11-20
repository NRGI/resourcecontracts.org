var AnnotatorjsView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.currentPage = options.currentPage;
        this.currentPage.on('change:page', this.changePage, this);        
        this.contract = options.contract;
        this.api = options.api,
        // this.listenTo(this.model, 'change:text', this.pageUpdated);
        this.content = $(this.options.el).annotator({
            readOnly: !this.options.contract.canAnnotate()
        });
        var self = this;
        this.content.data('annotator').setupAnnotation = function(annotation) {
            if (annotation.ranges !== undefined || $.isEmptyObject(annotation)) {
                return self.content.data('annotator').__proto__.setupAnnotation.call(self.content.data('annotator'), annotation);
            }
        };
        this.content.annotator('addPlugin', 'MyTags');
        this.content.annotator('addPlugin', 'AnnotatorEvents');
        this.content.data('annotator').plugins.MyTags.availableTags = options.availableTags
        this.content.data('annotator').plugins.AnnotatorEvents.collection = options.collection;
        this.content.data('annotator').plugins.AnnotatorEvents.currentPage = this.currentPage;
        this.annotationCategories = options.annotationCategories;
        this.populateCategories();
        this.setupStore();
        return this;
    },
    changePage: function() {
        var store = this.content.data('annotator').plugins.Store;
        store.options.annotationData = {
            'contract': this.contract.get('id'),
            'page': this.currentPage.getPage(),
            // 'page_id': that.model.get('id')
        };
    },
    populateCategories: function() {
        this.content.annotator('addPlugin', 'Categories', {
            category: this.annotationCategories.invoke("pick", ["key","name"])
        });
    },
    setupStore: function() {
        var self = this;
        this.content.annotator('addPlugin', 'Store', {
            // The endpoint of the store on your server.
            prefix: '/api',
            // Attach the uri of the current page to all annotations to allow search.
            loadFromSearch: {
                'contract': self.contract.get('id'),
                'page': 0
            },
            annotationData: {
                'contract': self.contract.get('id'),
                'page': self.currentPage.getPage(),
            }
        });
        return this;
    },
    // pageUpdated: function() {
    //     var that = this;
    //     var page = that.model.get('pageNumber');
    //     if (this.content.data('annotator').plugins.Store) {
    //         var store = this.content.data('annotator').plugins.Store;
    //         if (store.annotations) store.annotations = [];
    //         store.options.loadFromSearch = {
    //             'url': that.api,
    //             'contract': that.options.contract.get('id'),
    //             'page': 1,//that.model.get('pageNumber'),
    //             'page': 1,//that.model.get('pageNumber')
    //         };
    //         store.options.annotationData = {
    //             'url': that.api,
    //             'contract': that.options.contract.get('id'),
    //             'page': 1,//that.model.get('pageNumber'),
    //             'page': 1,//that.model.get('pageNumber'),
    //             // 'page_id': that.model.get('id')
    //         };
    //         store.loadAnnotationsFromSearch(store.options.loadFromSearch)
    //     } else {
    //         this.content.annotator('addPlugin', 'Store', {
    //             // The endpoint of the store on your server.
    //             prefix: '/api',
    //             // Attach the uri of the current page to all annotations to allow search.
    //             loadFromSearch: {
    //                 'url': that.api,
    //                 'contract': that.options.contract.get('id'),
    //                 'page': 1,
    //                 'page': 1
    //             },
    //             annotationData: {
    //                 'url': that.api,
    //                 'contract': that.options.contract.get('id'),
    //                 'page': 1,
    //                 'page': 1,
    //                 // 'page_id': that.model.get('id')
    //             }
    //         });
    //     }
    // },
    render: function() {
    }
});

var PdfAnnotatorjsView = AnnotatorjsView.extend({
   initialize: function(options){
       // _.extend(this.events, AnnotatorjsView.prototype.events);
       AnnotatorjsView.prototype.initialize(options);
       _.extend(this, AnnotatorjsView);
        if(options.enablePdfAnnotation) {
            this.content.annotator('addPlugin', 'AnnotoriousImagePlugin');
        }
        this.currentPage = options.currentPage;
        this.listenTo(this.currentPage, 'change:page', this.pageUpdated);
        this.pageUpdated();
   },
   pageUpdated: function() {
        var that = this;
        var page = this.currentPage.getPage();
        if (this.content.data('annotator').plugins.Store) {
            var store = this.content.data('annotator').plugins.Store;
            if (store.annotations) store.annotations = [];
            store.options.loadFromSearch = {
                'contract': that.contract.get('id'),
                'page': page
            };
            store.options.annotationData = {
                'contract': that.contract.get('id'),
                'page': page,
                // 'page_id': that.model.get('id')
            };
            store.loadAnnotationsFromSearch(store.options.loadFromSearch)
        } else {
            this.setupStore();
        }    
    }
});