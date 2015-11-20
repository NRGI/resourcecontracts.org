Annotator.Plugin.AnnotatorEvents = (function(_super) {
    __extends(AnnotatorEvents, _super);
    AnnotatorEvents.prototype.events = {
        'annotationCreated': 'onAnnotationCreated',
        'annotationDeleted': 'onAnnotationDeleted',
        'annotationUpdated': 'onAnnotationUpdated',
        'annotorious:annotation-clicked': 'onAnnotationClicked',
    };
    AnnotatorEvents.prototype.field = null;
    AnnotatorEvents.prototype.input = null;    
    AnnotatorEvents.prototype.pluginInit = function(options) {
        var annotator = this.annotator;
        if (!Annotator.supported()) {
            return;
        }      
        annotator.viewer.addField({
            load: this.updateViewer,
        });       
    };
    AnnotatorEvents.prototype.options = {
        AnnotatorEvents: {}
    };
    function AnnotatorEvents(element, options) {
        this.onAnnotationClicked = __bind(this.onAnnotationClicked, this);
        this.onAnnotationCreated = __bind(this.onAnnotationCreated, this);
        this.onAnnotationUpdated = __bind(this.onAnnotationUpdated, this);
        this.onAnnotationDeleted = __bind(this.onAnnotationDeleted, this);
        AnnotatorEvents.__super__.constructor.apply(this, arguments);
    };
    AnnotatorEvents.prototype.onAnnotationClicked = function(obj) {
        this.contractApp.trigger("annotations:highlight", obj.annotation);
    };
    AnnotatorEvents.prototype.onAnnotationCreated = function(annotation) {
        annotation.page = this.contractApp.getCurrentPage();
        annotation.category = annotation.category.trim();
        var self = this;
        setTimeout(function (event) {
            self.contractApp.trigger('annotationCreated', annotation); 
        }, 500);        
    };
    AnnotatorEvents.prototype.onAnnotationUpdated = function(annotation) {
        this.contractApp.trigger('annotationUpdated', annotation);
    };
    AnnotatorEvents.prototype.onAnnotationDeleted = function(annotation) {
        this.contractApp.trigger('annotationDeleted', annotation);
    };
    return AnnotatorEvents;
})(Annotator.Plugin);