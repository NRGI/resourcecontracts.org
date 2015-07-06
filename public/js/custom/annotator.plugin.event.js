  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  Annotator.Plugin.AnnotatorEvents = (function(_super) {
    __extends(AnnotatorEvents, _super);

    AnnotatorEvents.prototype.events = {
      // 'annotationsLoaded': 'onAnnotationsLoaded',
      'annotationCreated': 'onAnnotationCreated',
      'annotationDeleted': 'onAnnotationDeleted',
      'annotationUpdated': 'onAnnotationUpdated',
      // ".annotator-viewer-delete click": "onDeleteClick",
      // ".annotator-viewer-edit click": "onEditClick",
      // ".annotator-viewer-delete mouseover": "onDeleteMouseover",
      // ".annotator-viewer-delete mouseout": "onDeleteMouseout",
    };

    AnnotatorEvents.prototype.pluginInit = function() {
      if (!Annotator.supported()) {
        return;
      }
    };


    AnnotatorEvents.prototype.field = null;

    AnnotatorEvents.prototype.input = null;

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
        setTimeout(function(){
          //settime out so that the annotation gets saved
          annotationsCollection.add(annotation);
          annotationsCollection.trigger('annotationCreated');
        }, 1000);
    };

    AnnotatorEvents.prototype.onAnnotationUpdated = function(annotation) {
        annotationsCollection.add(annotation,{merge: true});
        annotationsCollection.trigger('annotationUpdated');
    };

    AnnotatorEvents.prototype.onAnnotationDeleted = function(annotation) {
        annotationsCollection.remove(annotation);
        annotationsCollection.trigger('annotationDeleted');
    };

    return AnnotatorEvents;

  })(Annotator.Plugin);    