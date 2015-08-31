Annotator.Plugin.AnnotatorEvents = (function(_super) {
    __extends(AnnotatorEvents, _super);
    AnnotatorEvents.prototype.events = {
        'annotationCreated': 'onAnnotationCreated',
        'annotationDeleted': 'onAnnotationDeleted',
        'annotationUpdated': 'onAnnotationUpdated',
    };
      AnnotatorEvents.prototype.field = null;
      AnnotatorEvents.prototype.input = null;    
    AnnotatorEvents.prototype.pluginInit = function(options) {
        if (!Annotator.supported()) {
            return;
        }      
    };
    AnnotatorEvents.prototype.options = {
        AnnotatorEvents: {}
    };

    function AnnotatorEvents(element, options) {
        // this.beforeAnnotationCreated = __bind(this.beforeAnnotationCreated, this);
        this.onAnnotationCreated = __bind(this.onAnnotationCreated, this);
        this.onAnnotationUpdated = __bind(this.onAnnotationUpdated, this);
        this.onAnnotationDeleted = __bind(this.onAnnotationDeleted, this);
        AnnotatorEvents.__super__.constructor.apply(this, arguments);
    };
    // AnnotatorEvents.prototype.beforeAnnotationCreated = function(annotation) {
    //     if(this.currentPage) {
    //         annotation.page = this.currentPage.getPage();
    //         // annotation.page_id = ;
    //     }
    //     return annotation;    
    //     // this.collection.add(annotation);
    // };
    AnnotatorEvents.prototype.onAnnotationCreated = function(annotation) {
        annotation.id = this.collection.length + 1;
        if(this.currentPage) {
            this.currentPage.trigger("")
            annotation.page = this.currentPage.getPage();
            // annotation.page_id = ;
        }
        var self = this;
        setTimeout(function (event) {
            self.collection.trigger('annotationCreated', annotation);    
        }, 500);        
        // this.collection.add(annotation);
    };
    AnnotatorEvents.prototype.onAnnotationUpdated = function(annotation) {
        this.collection.add(annotation, {
            merge: true
        });
        this.collection.trigger('annotationUpdated');
    };
    AnnotatorEvents.prototype.onAnnotationDeleted = function(annotation) {
        this.collection.remove(annotation);
        this.collection.trigger('annotationDeleted');
    };
    return AnnotatorEvents;
})(Annotator.Plugin);