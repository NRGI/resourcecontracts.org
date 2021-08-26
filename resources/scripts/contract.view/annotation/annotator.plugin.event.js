Annotator.Plugin.AnnotatorEvents = (function (_super) {
    __extends(AnnotatorEvents, _super);
    AnnotatorEvents.prototype.events = {
        'annotationCreated': 'onAnnotationCreated',
        'annotationDeleted': 'onAnnotationDeleted',
        'annotationUpdated': 'onAnnotationUpdated',
        'annotationsLoaded': 'annotationsLoaded',
        'annotationEditorSubmit': 'onAnnotationEditorSubmit',
        'annotorious:annotation-clicked': 'onAnnotationClicked',
        'annotorious:mouse-over-annotation': 'onMouseOverAnnotation'
    };
    AnnotatorEvents.prototype.field = null;
    AnnotatorEvents.prototype.input = null;
    AnnotatorEvents.prototype.pluginInit = function (options) {
        var annotator = this.annotator;
        if (!Annotator.supported()) {
            return;
        }
        annotator.viewer.addField({
            load: this.updateViewer
        });

        this.annotator
            .subscribe("annotationEditorShown", onEditorShownHandler)
            .subscribe("annotationViewerShown", onViewShownHandler);

        this.notification = new Annotator.Notification;

    };
    AnnotatorEvents.prototype.options = {
        AnnotatorEvents: {}
    };
    function AnnotatorEvents(element, options) {
        this.onAnnotationClicked = __bind(this.onAnnotationClicked, this);
        this.onAnnotationCreated = __bind(this.onAnnotationCreated, this);
        this.onAnnotationUpdated = __bind(this.onAnnotationUpdated, this);
        this.onAnnotationDeleted = __bind(this.onAnnotationDeleted, this);
        this.annotationsLoaded = __bind(this.annotationsLoaded, this);
        this.onMouseOverAnnotation = __bind(this.onMouseOverAnnotation, this);
        this.onAnnotationEditorSubmit = __bind(this.onAnnotationEditorSubmit, this);
        AnnotatorEvents.__super__.constructor.apply(this, arguments);
    }

    AnnotatorEvents.prototype.onAnnotationClicked = function (obj) {
        this.contractApp.trigger("annotations:highlight", obj.annotation);
    };
    AnnotatorEvents.prototype.onAnnotationCreated = function (annotation) {
        annotation.page = this.contractApp.getCurrentPage();
        annotation.category = annotation.category.trim();
        var self = this;
        setTimeout(function (event) {
            self.contractApp.trigger('annotationCreated', annotation);
            self.notification.show(LANG.annotation_successfully_created, 'success');
            publishAnnotation(self);
        }, 1000);


    };
    AnnotatorEvents.prototype.onAnnotationUpdated = function (annotation) {
        var self = this;
        setTimeout(function (event) {
            self.contractApp.setPdfLoaded(false);
            self.contractApp.trigger('annotationUpdated', annotation);
            self.notification.show(LANG.annotation_successfully_updated, 'success');
            publishAnnotation(self);
        }, 1000);        
    };
    AnnotatorEvents.prototype.onAnnotationDeleted = function (annotation) {
        var self = this;
        setTimeout(function () {
            if (typeof annotation.annotation_id !== 'undefined') {
                self.contractApp.trigger('annotationDeleted', annotation);
                self.notification.show(LANG.annotation_successfully_deleted, 'success');
            }
            publishAnnotation(self);
        }, 1000);
    };

    AnnotatorEvents.prototype.onAnnotationEditorSubmit = function (editor, annotation) {
    };

    AnnotatorEvents.prototype.onMouseOverAnnotation = function (viewer) {
        onViewShownHandler(viewer.mouseEvent)
    };
    AnnotatorEvents.prototype.annotationsLoaded = function (obj) {
        var annotation_id = contractApp.getSelectedAnnotation();
        var hash = window.location.hash;

        debug('annotation loaded');
        if (annotation_id === 0 && hash != '') {
            annotation_id = getAnnotationIdFromHash();
        }

        if (contractApp.getView() == 'pdf') {
            contractApp.showPdfAnnotationPopup(annotation_id);
        }

        if (contractApp.getView() == 'text') {
            contractApp.showTextAnnotationPopup(annotation_id);
        }
    };

    function getAnnotationIdFromHash() {
        var annotation_id = '';
        var hash = window.location.hash;

        if (typeof hash.split('annotation/')[1] !== 'undefined') {
            annotation_id = hash.split('annotation/')[1];
        }

        return annotation_id;
    }

    function onEditorShownHandler(viewer) {
        var widgetTextarea = $(".annotator-editor .annotator-widget textarea");
        var widget = $(".annotator-editor .annotator-widget");

        widget.outerHeight("auto").width("auto");
        widgetTextarea.outerHeight(75);

        $('.annotator-widget input').keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
            }
        });

        $('.annotator-cancel').text(LANG.cancel);
        $('.annotator-save').text(LANG.save);

        $('.annotator-widget').find('.error').remove();

        var viewPort = contractApp.getView() == 'pdf' ? 'pdf' : 'text';
        var viewerEl = $(viewer.element);

        var position = viewerEl.position();
        var wrapperEl = $('.' + viewPort + '-annotator');
        var widgetEl = wrapperEl.find('form.annotator-widget');
        var editorEl = wrapperEl.find('div.annotator-editor');
        var widgetHeight = widgetEl.height() + 25;

        if (wrapperEl.width() / 2 < position.left) {
            viewerEl.addClass('annotator-invert-x');
            editorEl.addClass('annotator-invert-x');
        } else {
            viewerEl.removeClass('annotator-invert-x');
            editorEl.removeClass('annotator-invert-x');
        }

        var diff = position.top - wrapperEl.scrollTop();

        if (diff < widgetHeight) {
            viewerEl.addClass('annotator-invert-y');
            editorEl.addClass('annotator-invert-y');
        } else {
            viewerEl.removeClass('annotator-invert-y');
            editorEl.removeClass('annotator-invert-y');
        }
        widget.resizable({
            resize: function (event, ui) {
                if (ui.size.height > 225) {
                    widgetTextarea.outerHeight(75 + (ui.size.height - 225));
                }
            }
        });
    }

    function onViewShownHandler(viewer, annotations) {
        var viewerEl = $(viewer.element);
        var viewPort = contractApp.getView() == 'pdf' ? 'pdf' : 'text';
        var position = viewerEl.position();
        var wrapperEl = $('.' + viewPort + '-annotator');
        var widgetEl = wrapperEl.find('ul.annotator-widget');
        var widgetHeight = widgetEl.height() + 25;

        $('.annotator-edit').attr('title', LANG.annotator_edit);
        $('.annotator-delete').attr('title', LANG.delete);

        if (wrapperEl.width() / 2 < position.left) {
            viewerEl.addClass('annotator-invert-x');
            widgetEl.addClass('annotator-invert-x');
        } else {
            viewerEl.removeClass('annotator-invert-x');
            widgetEl.removeClass('annotator-invert-x');
        }

        var diff = position.top - wrapperEl.scrollTop();

        if (diff < widgetHeight) {
            viewerEl.addClass('annotator-invert-y');
            widgetEl.addClass('annotator-invert-y');
        } else {
            viewerEl.removeClass('annotator-invert-y');
            widgetEl.removeClass('annotator-invert-y');
        }
    }

    function publishAnnotation(self){
        $.ajax({
            url: self.publishApi,
            data: {
                type : 'annotation'
            },
            type: 'POST'
        }).success(function(response){
            if(response.publish_status){
                self.notification.show(response.message, 'success');
            }
        });
    }

    return AnnotatorEvents;
})(Annotator.Plugin);
