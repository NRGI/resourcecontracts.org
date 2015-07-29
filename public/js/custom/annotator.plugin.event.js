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
        this.onAnnotationCreated = __bind(this.onAnnotationCreated, this);
        this.onAnnotationUpdated = __bind(this.onAnnotationUpdated, this);
        this.onAnnotationDeleted = __bind(this.onAnnotationDeleted, this);
        AnnotatorEvents.__super__.constructor.apply(this, arguments);
    };
    AnnotatorEvents.prototype.onAnnotationCreated = function(annotation) {
        console.log("Created", annotation);
        annotation.id = this.collection.length + 1;
        if(this.pageModel) {
            annotation.page = this.pageModel.get('pageNumber');
            annotation.page_id = this.pageModel.get('id');
        }
        var self = this;
        setTimeout(function (event) {
            self.collection.trigger('annotationCreated');    
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