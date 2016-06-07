Annotator.Plugin.PdfAnnotator = (function (_super) {
    __extends(PdfAnnotator, _super);
    PdfAnnotator.prototype.events = {
        'annotationEditorSubmit': 'onAnnotationEditorSubmit'
    };
    PdfAnnotator.prototype.field = null;
    PdfAnnotator.prototype.notification = null;
    PdfAnnotator.prototype.pluginInit = function (options) {
        var annotator = this.annotator;
        if (!Annotator.supported()) {
            return;
        }
        var el = annotator.element;
        var boxEl = el.find('.annotator-wrapper');
        var self = this;

        var enableDragableResizable = function(){
            el.find('div.annotator-hl').resizable({
                helper: "ui-resizable-helper", stop: updateChange
            });
            el.find('div.annotator-hl').draggable({cursor: "move", scroll: true, stop: updateChange});
        };

        annotator.subscribe("annotationCreated", enableDragableResizable);

        annotator.subscribe("annotationsLoaded", function (annotation) {
            boxEl.find('div.annotator-hl').remove();
            annotation.map(function (ann) {
                if (ann.shapes === undefined)
                    return '';
                self.annotationLoader(ann);
            });

            enableDragableResizable();
        });

        annotator.subscribe("annotationDeleted", function (annotation) {
            $('div.annotator-hl').each(function () {
                var obj1 = JSON.stringify($(this).data('annotator'));
                var obj2 = JSON.stringify(annotation);
                if (obj1 === obj2) {
                    $(this).remove();
                }
            })
        });

        annotator.subscribe("annotationEditorShown", function () {
            boxEl.boxer({disabled: true});
            boxEl.find('div.annotator-hl').draggable({disabled: true});
            boxEl.find('div.annotator-hl').resizable({disabled: true});
        });

        annotator.subscribe("annotationEditorHidden", function () {
          setTimeout(function(){
            boxEl.boxer({disabled: false});
            boxEl.find('div.annotator-hl').draggable({disabled: false});
            boxEl.find('div.annotator-hl').resizable({disabled: false});
          }, 2100);
        });

        var updateChange = function (e, ele) {
            var el = $(e.target);
            $(annotator.viewer.element).addClass('annotator-hide');

            if(e.type == 'resizestop'){
              $(e.target).css('height',(el.height()+12+'px'));
              $(e.target).css('width',(el.width()+12+'px'));
            }

            var shape = [];
            shape.top = el.offset().top;
            shape.left = el.offset().left;
            shape.height = el.height();
            shape.width = el.width();

            boxEl.boxer({disabled: true});
            $(e.target).find('div.annotator-resize-action').remove();
            boxEl.find('div.annotator-hl').removeClass('resizable-active');
            $(e.target).addClass('resizable-active');
            $(e.target).append(self.resizeButtons());
            var hl = el.find('div.annotator-hl').draggable( "option", "disabled", true );
            boxEl.find('div.annotator-hl').draggable({disabled: true});
            boxEl.find(e.target).draggable({disabled: false});

            boxEl.find('div.annotator-hl').resizable({disabled: true});
            boxEl.find(e.target).resizable({disabled: false});
        };

        $(document).on('click', '.annotator-resize-action button.cancel', function () {
            var el = $(this).parent().parent();
            var annotator = el.data('annotator');
            var shape = annotator.shapes[0].geometry;
            shape = self.getShape(shape);
            el.css({top: shape.y, left: shape.x, height: shape.height, width: shape.width});
            el.find('.annotator-resize-action').remove();
            if (boxEl.find('.cancel').length === 0) {
                boxEl.boxer({disabled: false});
            }

            boxEl.find('div.annotator-hl').draggable({disabled: false});
            boxEl.find('div.annotator-hl').resizable({disabled: false});

        });

        $(document).on('click', '.annotator-resize-action .standardSize', function(){
            var el = $(this).parent().parent();
            el.css('height', '25px');
            el.css('width', '40px');
        });

        $(document).on('click', '.annotator-resize-action button.save', function () {
            var el = $(this).parent().parent();
            var annotator = el.data('annotator');
            var geometry = [];
            geometry.y = parseInt(el.css('top'));
            geometry.x = parseInt(el.css('left'));
            geometry.height = parseInt(el.css('height'));
            geometry.width = parseInt(el.css('width'));
            annotator.shapes[0].geometry = self.getGeoInPercentage(geometry);
            self.annotator.publish('annotationUpdated', annotator);
            el.find('button').remove();

            boxEl.find('div.annotator-hl').draggable({disabled: true});
            boxEl.find('div.annotator-hl').resizable({disabled: true});

            setTimeout(function(){
                  if (boxEl.find('.save').length === 0) {
                      boxEl.boxer({disabled: false});
                  }
                  boxEl.find('div.annotator-hl').draggable({disabled: false});
                  boxEl.find('div.annotator-hl').resizable({disabled: false});
            }, 2100);
        });

        var data = boxEl.boxer({
            stop: function (event, ui) {
                var offset = [];
                offset.top = parseInt(ui.box.css('top'));
                offset.left = parseInt(ui.box.css('left'));
                offset.height = parseInt(ui.box.css('height'));
                offset.width = parseInt(ui.box.css('width'));
                ui.box.addClass('annotator-raw');
                var pos = {};
                pos.top = offset.top + offset.height / 2;
                pos.left = offset.left + offset.width / 2;
                annotator.showEditor({shapes: self.getShapeDataFormat(offset), box: ui.box}, pos);
            }
        });

        $('.annotator-controls').on('click', 'a.annotator-cancel', function () {
            $('.annotator-raw').remove();
            boxEl.boxer({disabled: false});
        });

        el.on('mouseover', 'div', function () {
            if ($(this).find('.save').length > 0) {
                $(annotator.viewer.element).addClass('annotator-hide');
                return '';
            }

            var annotation = $(this).data('annotator');

            if (annotation) {
                var pos = {};
                pos.top = parseInt($(this).css('top')) + parseInt($(this).height());
                pos.left = parseInt($(this).css('left')) + parseInt($(this).width() / 2);
                annotator.showViewer([annotation], pos);
            }
        })

    };

    PdfAnnotator.prototype.getShapeDataFormat = function (offset) {
        return [{type: "rect", geometry: {x: offset.left, y: offset.top, height: offset.height, width: offset.width}}];
    };

    PdfAnnotator.prototype.resizeButtons = function()
    {
        return '<div class="btn-group annotator-resize-action" role="group">' +
                    '<button title="'+LANG.save+'" class="save btn btn-primary"><span class="glyphicon glyphicon-ok"' +
            ' aria-hidden="true"></span></button> ' +
                    '<button title="'+LANG.cancel+'"  class="cancel btn btn-danger" ><span class="glyphicon' +
            ' glyphicon-remove" aria-hidden="true"></span></button>' +
                    '<button title="'+LANG.standard_box+'"class="standardSize btn btn-warning" ><span' +
            ' class="glyphicon' +
            ' glyphicon-unchecked" aria-hidden="true"></span></button>' +
                '</div>';
    };

    PdfAnnotator.prototype.annotationLoader = function (annotation) {
        var geo = annotation.shapes[0].geometry;

        geo = this.getShape(geo);

        var div = $('<div></div>')
            .appendTo(this.annotator.element.find('.annotator-wrapper'))
            .data('annotator', annotation)
            .addClass('annotator-hl')
            .addClass('annotator-pdf-hl')
            .css({position: 'absolute', left: geo.x, top: geo.y, height: geo.height, width: geo.width});
    }

    PdfAnnotator.prototype.options = {
        PdfAnnotator: {}
    };

    function PdfAnnotator(element, options) {
        this.onAnnotationEditorSubmit = __bind(this.onAnnotationEditorSubmit, this);
        PdfAnnotator.__super__.constructor.apply(this, arguments);
    }

    PdfAnnotator.prototype.getShape = function (geometry) {
        var canvas = this.annotator.element.find('canvas');
        var g = {};
        g.x = geometry.x * canvas.width();
        g.y = geometry.y * canvas.height();
        g.height = geometry.height * canvas.height();
        g.width = geometry.width * canvas.width();
        return g;
    };

    PdfAnnotator.prototype.getGeoInPercentage = function (geometry) {
        var canvas = this.annotator.element.find('canvas');
        var g = {};
        g.x = geometry.x / canvas.width();
        g.y = geometry.y / canvas.height();
        g.height = geometry.height / canvas.height();
        g.width = geometry.width / canvas.width();
        return g;
    };

    PdfAnnotator.prototype.onAnnotationEditorSubmit = function (editor, annotation) {
        if(editor.annotation.box !== undefined)
        {
          var hl = editor.annotation.box;
          delete editor.annotation.box;
          hl.data('annotator', editor.annotation);
          hl.removeClass('annotator-raw').addClass('annotator-hl').addClass('annotator-pdf-hl');
          var geometry = editor.annotation.shapes[0].geometry;
          editor.annotation.shapes[0].geometry = this.getGeoInPercentage(geometry);
          this.annotator.publish('annotationCreated', editor.annotation);
        }
    };
    return PdfAnnotator;
})(Annotator.Plugin);
