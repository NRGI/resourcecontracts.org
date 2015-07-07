    var AnnotatorjsView = Backbone.View.extend({
    initialize: function(options) {
        this.options = options;
        this.listenTo(this.options.pageModel, 'pageChange', this.pageUpdated);
        this.bind('pageChange', this.pageUpdated);
        this.content = $(this.options.annotatorjsEl).annotator({readOnly: !this.options.pageModel.get('isAnnotable')});
        this.content.annotator('addPlugin', 'Tags');
        this.content.annotator('addPlugin', 'AnnotatorEvents');
        this.content.annotator('addPlugin', 'Categories', {category:this.options.categories});
        this.availableTags = this.options.tags;
        return this;
    },
    pageUpdated: function() {
        var that = this;
        var store = this.content.data('annotator').plugins.Store;
        if(store.annotations) store.annotations = [];

        store.options.loadFromSearch = { 
            'url': that.options.contractModel.get('annotatorjsAPI'),
            'contract': that.options.contractModel.get('id'),
            'page': that.options.pageModel.get('pageNumber'),
            'document_page_no': that.options.pageModel.get('pageNumber')
        };            
        store.options.annotationData = { 
            'url': that.options.contractModel.options.annotatorjsAPI,
            'contract': that.options.contractModel.get('id'),
            'document_page_no': that.options.pageModel.get('pageNumber'),
            'page': that.options.pageModel.get('pageNumber'),
            'page_id': that.options.pageModel.get('id')
        }
        store.loadAnnotationsFromSearch(store.options.loadFromSearch)                

    },
    render: function() {
        var that = this;
        var page = that.options.pageModel.get('pageNumber');
        if(this.content.data('annotator').plugins.Store) {
            var store = this.content.data('annotator').plugins.Store;
            if(store.annotations) store.annotations = [];
            store.options.loadFromSearch = { 
                'url': that.options.contractModel.get('annotatorjsAPI'),
                'contract': that.options.contractModel.get('id'),
                'page': that.options.pageModel.get('pageNumber'),
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
                    'page': that.options.pageModel.get('pageNumber'),
                    'document_page_no': that.options.pageModel.get('pageNumber')                    
                },
                annotationData: {
                    'url': that.options.contractModel.get('annotatorjsAPI'),
                    'contract': that.options.contractModel.get('id'),
                    'page': that.options.pageModel.get('pageNumber'),
                    'document_page_no': that.options.pageModel.get('pageNumber'),
                    'page_id': that.options.pageModel.get('id')
                }
            }); 
        }
        function split( val ) {
            return val.split( / \s*/ );
        }
        function extractLast( term ) {
            return split( term ).pop();
        }
        var availableTags = this.availableTags;
        this.content.data('annotator').plugins.Tags.input.autocomplete({
            source: function( request, response) {
                // delegate back to autocomplete, but extract the last term
                response( $.ui.autocomplete.filter(
                        availableTags, extractLast( request.term ) ) );
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var terms = split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                terms.push( "" );
                this.value = terms.join( " " );
                return false;
            }
        });        
        return this;
    }                   
});

var MyAnnotation = Backbone.Model.extend();

var MyAnnotationCollection = Backbone.Collection.extend({
    model: MyAnnotation,
    fetch: function(options, callback) {
        if(options.contractModel) {
            this.reset();
            var self = this;
            $.ajax({
                url : options.contractModel.getAnnotationPullAPI(),
                type : "GET",
                dataType: "json",
            }).done(function(response){   
                for(var i=0;i<response.length;i++) {
                    self.add(response[i]);
                }
                if(callback)
                    callback();
            });
        }
        return this;
    },
});

var AnnotationSideView = Backbone.View.extend({
    tagName: 'div',
    initialize: function(options) {
        this.options = options;
    },
    events: {
        "click a":"changePage"
    },
    render: function() {
        this.$el.append('<a href="#">'+this.model.get('quote')+'</a>[Page '+this.model.get('page')+']<br><p>'+this.model.get('text')+'</p>');
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
        this.listenTo(this.collection, 'annotationCreated', this.render);
        this.listenTo(this.collection, 'annotationUpdated', this.updateView);
        this.listenTo(this.collection, 'annotationDeleted', this.updateView);
    },
    updateView: function() {
        var self = this;
        annotationCategories = self.collection.groupBy('category');
        self.$el.html('');
        for(var annotationCategoryCollection in annotationCategories) {
            self.$el.append('<h3>' + annotationCategoryCollection + '</h3>');
            _.each(annotationCategories[annotationCategoryCollection], function(annotation) {
                if(!annotation.get('page') || annotation.get('page') == "" )
                    annotation.set('page', self.options.pageModel.get('pageNumber'));
                var annotationSideView = new AnnotationSideView({ model: annotation, pageModel: self.options.pageModel});
                self.$el.append(annotationSideView.render().$el);
            })
        }
        if(self.collection.length <=0) {
            self.$el.html('No annotations');
        }
    },
    render: function() {
        var self = this;
        self.$el.html('loading');
    
        this.collection.fetch({contractModel: self.options.contractModel}, function() {
            self.updateView();
        });
        return this;
    },
    toggle: function() {
        this.$el.toggle();
        if(this.$el.is(':visible')) {
            this.render();
        } else {
        }
    },
    close: function() {
        this.$el.hide();
    }
});