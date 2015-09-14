var Annotation = Backbone.Model.extend({});
var AnnotationCollection = Backbone.Collection.extend({
    model: Annotation
});
var AnnotationCategory = Backbone.Model.extend({});
var AnnotationCategories = Backbone.Collection.extend({
    model: AnnotationCategory
});
var AnnotationItemView = Backbone.View.extend({
    tagName: "li",
    className: 'annotation-item',
    events: {
        "click a": "changePage",
    },
    initialize: function(options) {
        this.template = _.template($('#annotation-item-view-template').html());
        this.eventsPipe = options.eventsPipe;        
    },
    render: function() {
        this.$el.html(this.template(this.model.attributes));
        return this;
    },
    changePage: function(e) {
        e.preventDefault();
        if(this.model.get('ranges')) {
            var annotatorStartPos = $('.annotator-text').xpath("/" + this.model.get('ranges')[0]["start"]);
            this.eventsPipe.trigger('scroll-to-annotation', annotatorStartPos[annotatorStartPos.length-1]);
        } 
        if(this.model.get('shapes')) {
            this.eventsPipe.trigger("page-change", this.model.get('page'));
        }
        // $("#text-viewer-overflow-scroll").animate(            
        //     {scrollTop: $(this.el).scrollTop() + ($(annotatorStartPos).offset().top - $(this.el).offset().top) - $(this.el).height()/2 + $(annotatorStartPos).height()/2
        // }, 50);

        // scrollIntoView(this.model.get('ranges')[0]);

        
    },
});
var AnnotationsListView = Backbone.View.extend({
    events: {
        "click #done-annotated": 'resetListView',
        "click #not-annotated": 'resetListView',
        "click #all": 'resetListView',
    },
    initialize: function(options) {
        this.eventsPipe = options.eventsPipe;        
        this.annotationCategories = options.annotationCategories;
        this.annotationsTitleView = options.annotationsTitleView;
        this.listenTo(this.annotationCategories, 'resetAnnotationsList', this.resetAnnotationsList);
        this.listenTo(this.collection, 'reset', this.render);
        this.listenTo(this.collection, 'annotationCreated', this.annotationCreated);
        this.listenTo(this.collection, 'annotationUpdated', this.render);
        this.listenTo(this.collection, 'annotationDeleted', this.render);
        return this;
    },
    annotationCreated: function() {
        this.collection.fetch({reset: true});
    },
    resetListView: function(e) {
        this.annotationsTitleView.resetListView(e);
    },
    resetAnnotationsList: function(e) {
        var annotationsByCategories = this.collection.groupBy('category');
        switch(e) {
            case 'done-annotated':
                $(this.el).html(this.annotationsTitleView.el);
                this.renderAnnotationWithCategories(annotationsByCategories);
                if(_.keys(annotationsByCategories).length === 0) {
                    $(this.el).append('Oops! You haven\'t annotated anything');
                }
                break;
            case 'not-annotated':
                $(this.el).html(this.annotationsTitleView.$el);
                this.renderBlankCategories(annotationsByCategories);
                break;
            default:
                this.render();                
        }
    },
    render: function() {
        var self = this;
        $(self.el).html('');
        $(self.el).append(this.annotationsTitleView.$el);
        var annotationsByCategories = this.collection.groupBy('category');

        this.renderBlankCategories(annotationsByCategories);
        this.renderAnnotationWithCategories(annotationsByCategories);
        return this;
    },
    renderBlankCategories: function(annotationsByCategories) {
        var t = _.template($('#annotation-category-no-items-template').html());
        var self = this;
        this.annotationCategories.each(function(category) {
            if (_.indexOf(_.keys(annotationsByCategories), category.get('name')) === -1) {
                $(self.el).append(t({
                    'categoryName': category.get('name').trunc(40)
                }));
            }
        });
    },
    renderAnnotationWithCategories: function(annotationsByCategories) {
        var self = this;
        if (annotationsByCategories !== undefined && _.keys(annotationsByCategories).length > 0) {
            var t = _.template($('#annotation-category-with-items-template').html());
            for (var annotationCategoryName in annotationsByCategories) {
                var annotationsGroup = annotationsByCategories[annotationCategoryName];
                annotationCategoryElemId = annotationCategoryName.replace(/\s+|;|:|,|#|\/|\.|\(|\)/g, '-');
                $(self.el).append(t({
                    'elemId': annotationCategoryElemId,
                    'categoryName': annotationCategoryName.trunc(40),
                    'categoryItemsCount': annotationsGroup.length
                }));
                _.each(annotationsGroup, function(annotation) {
                    $("#" + annotationCategoryElemId).append(new AnnotationItemView({
                        model: annotation,
                        eventsPipe: self.eventsPipe,
                    }).render().el);
                })
            }
        }
    },
    toggle: function() {
        this.$el.toggle();
    }    
});
var AnnotationsTitleView = Backbone.View.extend({
    events: {
        "click #done-annotated": 'resetListView',
        "click #not-annotated": 'resetListView',
        "click #all": 'resetListView',
    },
    initialize: function(options) {
        _.bindAll(this, 'resetListView', 'render');
        this.template = _.template($('#annotation-list-title-template').html());
        this.annotationCategories = options.annotationCategories;
        this.listenTo(this.collection, 'reset', this.render);
        this.listenTo(this.collection, 'annotationCreated', this.render);
        this.listenTo(this.collection, 'annotationUpdated', this.render);
        this.listenTo(this.collection, 'annotationDeleted', this.render);
        return this;
    },
    render: function() {
        var totalCategories = this.annotationCategories.length;
        var categoriesWithAnnotationCount = _.keys(this.collection.groupBy('category')).length;
        $(this.el).html(this.template({total: totalCategories, done: categoriesWithAnnotationCount, remaining: totalCategories-categoriesWithAnnotationCount }));
        return this;
    },
    resetListView: function(e) {
        $("button").removeClass('active');
        $("#"+e.target.id).addClass('active');
        e.preventDefault();
        this.annotationCategories.trigger('resetAnnotationsList', e.target.id);
    }
});
var AnnotationsButtonView = Backbone.View.extend({
    events: {
        'click': 'toggle'
    },
    initialize: function(options) {
        this.annotationsListView = options.annotationsListView;
    },
    toggle: function(e) {
        e.preventDefault();
        this.annotationsListView.toggle();
    },
});
