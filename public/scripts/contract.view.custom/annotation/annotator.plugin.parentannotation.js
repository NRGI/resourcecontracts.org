Annotator.Plugin.ParentAnnotation = (function (_super) {
    __extends(ParentAnnotation, _super);

    ParentAnnotation.prototype.pluginInit = function (options) {
        if (!Annotator.supported()) {
            return;
        }

        this.field = this.annotator.editor.addField({
            id: 'annotation-parent-dropdown',
            type: 'select',
            load: this.updateParentAnnotation,
            submit: this.saveParentAnnotation
        });

        this.annotator.viewer.addField({
            load: this.updateViewer,
            id: 'annotation-parent-viewer'
        });

        var self = this;
        $(document).on('change', '#annotation-plugin-select-category', function () {
            var annotation = $('.annotation-viewer-text').data('annotation');
            annotation.category = $(this).val();
            var select = getAnnotationSelect(annotation);
            $(self.field).find('.select2-selection__rendered').html('<span class="select2-selection__placeholder">Select parent annotation</span>');
            $(self.field).find('select').html(select);
        });

        $(document).on('click', '.parent_annotation_link', function () {
            var $this = $(this);
            if ($this.data('view') == 'text') {
                setTimeout(function () {
                    contractApp.showTextAnnotationPopup($this.data('annotation'));
                }, 300);
            }

            if ($this.data('view') == 'pdf') {
                setTimeout(function () {
                    contractApp.showPdfAnnotationPopup($this.data('annotation'));
                }, 300);
            }

        });
    };

    function ParentAnnotation() {
        ParentAnnotation.__super__.constructor.call(this, arguments);
    }

    function getAnnotationSelect(annotation) {
        var select = '<option value="">Select parent annotation</option>';
        var selected = "";

        var parents = annotationsCollection.parentAnnotations(annotation);

        parents.map(function (a) {
            selected = (a.get('id') == annotation.parent) ? 'selected="selected"' : '';
            select += '<option value="' + a.get('id') + '"' + selected + '>' + a.get('text') + '</option>';
        });

        return select;
    }

    function isParent(annotation_id) {
        var is = false;
        annotationsCollection.models.map(function (a) {
            if (a.get('parent') == annotation_id) {
                is = true;
            }
        });
        return is;
    }

    ParentAnnotation.prototype.saveParentAnnotation = function (el, annotation) {
        annotation.parent = $(el).find('select').val();
    };

    ParentAnnotation.prototype.updateParentAnnotation = function (el, annotation) {
        var select = getAnnotationSelect(annotation);

        if (isParent(annotation.id)) {
            $(el).find('select').hide();
            $(el).find('select').siblings('.select2').remove();
            return true;
        }

        $(el).find('select').show();

        if (typeof annotation.category === 'undefined') {
            setTimeout(function () {
                annotation.category = $('#annotation-plugin-select-category').val();
                select = getAnnotationSelect(annotation);
                $(el).find('select').html(select);
                $(el).find('select').select2({placeholder: 'Select parent annotation', allowClear: true, theme: "classic"});
            }, 100);
        } else {
            $(el).find('select').html(select);
            $(el).find('select').select2({placeholder: 'Select parent annotation', allowClear: true, theme: "classic"});
        }
    };

    ParentAnnotation.prototype.updateViewer = function (el, annotation) {
        var html = '';
        var annotations = '';

        if (annotation.parent != '') {
            annotations = annotationsCollection.relatedAnnotations(annotation);
        }
        else {
            annotations = annotationsCollection.childAnnotations(annotation);
        }

        if (annotations.length > 0) {
            html += '<p><strong>Related Annotation:</strong></p>';

            annotations.map(function (a) {
                var link = "";
                var view = "";
                if (a.get('shapes')) {
                    view = 'pdf';
                    link = "#/" + view + "/page/" + a.get('page') + "/annotation/" + a.get('id');
                } else {
                    view = 'text';
                    link = "#/" + view + "/page/" + a.get('page') + "/annotation/" + a.get('id');
                }
                html += '<p style="padding: 5px 0px;"><a data-view="' + view + '" data-annotation="' + a.get('id') + '" class="parent_annotation_link" href="' + link + '">' + a.get('text') + '</a></p>';
            });

            $(el).html(html);
        }


    };

    return ParentAnnotation;

})(Annotator.Plugin);
