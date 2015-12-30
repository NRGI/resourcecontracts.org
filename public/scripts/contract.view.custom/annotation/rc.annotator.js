var AnnotatorjsView = Backbone.View.extend({
    initialize: function (options) {
        var self = this;
        this.options = options;
        this.api = options.api,
            // this.listenTo(this.model, 'change:text', this.pageUpdated);
            this.content = $(this.options.el).annotator({
                readOnly: false
            });
        this.content.data('annotator').setupAnnotation = function (annotation) {
            if (annotation.ranges !== undefined || $.isEmptyObject(annotation)) {
                return self.content.data('annotator').__proto__.setupAnnotation.call(self.content.data('annotator'), annotation);
            }
        };
        this.contractApp = options.contractApp;


        // this.content.annotator('addPlugin', 'MyTags');
        this.content.annotator('addPlugin', 'AnnotatorEvents');
        this.content.annotator('addPlugin', 'AnnotatorNRGIViewer');
        this.content.annotator('addPlugin', 'Section');
        this.content.annotator('addPlugin', 'ParentAnnotation');

        // this.content.data('annotator').plugins.MyTags.availableTags = options.availableTags
        this.content.data('annotator').plugins.AnnotatorEvents.contractApp = options.contractApp;
        this.content.data('annotator').plugins.AnnotatorNRGIViewer.contractApp = options.contractApp;

        // this.content.data('annotator').plugins.AnnotatorEvents.currentPage = this.currentPage;
        // this.annotationCategories = options.annotationCategories;
        this.populateCategories();
        this.setupStore(options.enablePdfAnnotation || false);
        return this;
    },

    changePage: function () {
        var page_no = this.contractApp.getCurrentPage(),
            contract_id = this.contractApp.getContractId();
        var store = this.content.data('annotator').plugins.Store;
        store.options.annotationData = {
            'contract': contract_id,
            'page': page_no,
        };
    },
    populateCategories: function () {
        var annotationCategories = this.contractApp.getAnnotationCategories();
        this.content.annotator('addPlugin', 'Categories', {
            category: annotationCategories.invoke("pick", ["key", "name"])
        });
    },
    setupStore: function (enablePdfAnnotation) {
        var self = this;
        var page_no = this.contractApp.getCurrentPage(),
            contract_id = this.contractApp.getContractId();
        if (enablePdfAnnotation) {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                loadFromSearch: {
                    'contract': contract_id,
                    'page': page_no
                },
                annotationData: {
                    'contract': contract_id,
                    'page': page_no
                }
            });
        } else {
            this.content.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: '/api',
                // Attach the uri of the current page to all annotations to allow search.
                loadFromSearch: {
                    'contract': contract_id,
                },
                annotationData: {
                    'contract': contract_id,
                }
            });
        }

        return this;
    },
    reload: function () {
        var self = this;
        var page_no = this.contractApp.getCurrentPage(),
            contract_id = this.contractApp.getContractId();
        var store = this.content.data('annotator').plugins.Store;
        if (store.annotations) store.annotations = [];
        store.options.loadFromSearch = {
            'contract': contract_id,
        };
        store.options.annotationData = {
            'contract': contract_id,
        };

        store.loadAnnotationsFromSearch(store.options.loadFromSearch);
    }
});

var PdfAnnotatorjsView = AnnotatorjsView.extend({
    initialize: function (options) {
        // _.extend(this.events, AnnotatorjsView.prototype.events);
        AnnotatorjsView.prototype.initialize(options);
        _.extend(this, AnnotatorjsView);
        if (options.enablePdfAnnotation) {
            this.content.annotator('addPlugin', 'AnnotoriousImagePlugin');
        }
        var self = this;
        this.contractApp.on("annotationHighlight", function (annotation) {
            // if(self.contractApp.getCurrentPage() === annotation.page_no) {
            //     setTimeout(function() {
            //         console.log("starting publishing annotationHighlight");
            //         self.content.data('annotator').publish("annotationHighlight", annotation)
            //     }, 2000);
            // }
            // console.log("start", annotation);
            // setTimeout(function() {
            //     console.log("starting publishing annotationHighlight");
            //     self.content.data('annotator').publish("annotationHighlight", annotation)
            // }, 4000);
        });
        // this.listenTo(this.currentPage, 'change:page', this.pageUpdated);
        // this.pageUpdated();
    },
    pageUpdated: function () {
        var self = this;
        var page_no = this.contractApp.getCurrentPage(),
            contract_id = this.contractApp.getContractId();
        if (this.content.data('annotator').plugins.Store) {
            var store = this.content.data('annotator').plugins.Store;
            if (store.annotations) store.annotations = [];
            store.options.loadFromSearch = {
                'contract': contract_id,
                'page': page_no,
            };
            store.options.annotationData = {
                'contract': contract_id,
                'page': page_no,
            };
            store.loadAnnotationsFromSearch(store.options.loadFromSearch)
        } else {
            this.setupStore();
        }
    }
});