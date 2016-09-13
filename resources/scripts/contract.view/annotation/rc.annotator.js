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

        this.content.annotator('addPlugin', 'AnnotatorNRGIViewer');
        this.populateCategories();
        this.content.annotator('addPlugin', 'ParentAnnotation');
        this.content.annotator('addPlugin', 'ArticleReference');
        this.content.annotator('addPlugin', 'AnnotatorEvents');

        this.content.data('annotator').plugins.AnnotatorEvents.contractApp = options.contractApp;
        this.content.data('annotator').plugins.AnnotatorNRGIViewer.contractApp = options.contractApp;

        // this.content.data('annotator').plugins.AnnotatorEvents.currentPage = this.currentPage;
        // this.annotationCategories = options.annotationCategories;
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

        $('.text-annotator').find('span.annotator-hl').each(function () {
            $(this).replaceWith($(this).html());
        });

        var page_no = this.contractApp.getCurrentPage(),
            contract_id = this.contractApp.getContractId();
        var store = this.content.data('annotator').plugins.Store;
        store.annotations = [];

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

        AnnotatorjsView.prototype.initialize(options);

        _.extend(this, AnnotatorjsView);

        if (options.enablePdfAnnotation) {
            this.content.annotator('addPlugin', 'PdfAnnotator');
        }
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
            console.error('setupStore');
            this.setupStore();
        }
    }
});