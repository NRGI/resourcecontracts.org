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
            var category = $(this).val();
            var select = getAnnotationSelect(category, null);
            var dropdown = $(self.field);
            dropdown.find('.select2-selection__rendered').html('<span class="select2-selection__placeholder">Select parent annotation</span>');
            dropdown.find('select').html(select);
            dropdown.find('select').select2({placeholder: 'Select parent annotation', allowClear: true, theme: "classic"});
            var parents = annotationsCollection.parentAnnotations(category);
            dropdown.parent().find('textarea').val('');
            parents.map(function (text, id) {
                dropdown.parent().find('textarea').val(text);
            })
        });

        $(document).on('click', '.parent_annotation_link', function () {
            var $this = $(this);
            contractApp.trigger("annotations:highlight", {id: $this.data('annotation')});
            if ($this.data('view') == 'text') {
                setTimeout(function () {
                    contractApp.showTextAnnotationPopup($this.data('annotation'));
                }, 300);
            }
        });

    };

    function ParentAnnotation() {
        ParentAnnotation.__super__.constructor.call(this, arguments);
    }

    function getAnnotationSelect(category, annotation_id) {
        var select = '<option value="">Select parent annotation</option>';
        var selected = "";
        var parents = annotationsCollection.parentAnnotations(category);

        parents.map(function (text, id) {
            selected = (id == annotation_id || !annotation_id ) ? 'selected="selected"' : '';
            var textArr = text.split(" ");
            text = textArr.splice(0, 10).join(" ");
            if (textArr.length > 10) {
                text += '...';
            }
            select += '<option value="' + id + '"' + selected + '>' + text + '</option>';
        });

        return select;
    }

    ParentAnnotation.prototype.saveParentAnnotation = function (el, annotation) {
        annotation.annotation_id = parseInt($(el).find('select').val());
    };

    ParentAnnotation.prototype.updateParentAnnotation = function (el, annotation) {
        var select = getAnnotationSelect(annotation.category, annotation.annotation_id);
        $(el).hide();
        $(el).find('select').html(select);
        $(el).find('select').select2({placeholder: 'Select parent annotation', allowClear: true, theme: "classic"});
    };

    ParentAnnotation.prototype.updateViewer = function (el, annotation) {
        var html = '';
        var annotations = annotationsCollection.relatedAnnotations(annotation);

        if (annotations.length > 0) {
            var page = [];

            annotations.sort(function (a, b) {
                return a.get('page') - b.get('page');
            });

            var annotationGroupByPage = _.groupBy(annotations, function (a) {
                return a.get('page');
            });

            annotationGroupByPage = _.toArray(annotationGroupByPage);

            annotationGroupByPage.map(function (anno, index) {
                var a = anno[0];
                var last = false;
                if (index < (length - 1)) {
                    last = true;
                }

                var ref = [];
                anno.map(function (a, index) {
                    var link = "";
                    var view = "";
                    if (a.get('shapes')) {
                        view = 'pdf';
                        link = "#/" + view + "/page/" + a.get('page') + "/annotation/" + a.get('id');
                    } else {
                        view = 'text';
                        link = "#/" + view + "/page/" + a.get('page') + "/annotation/" + a.get('id');
                    }

                    var article_reference = (a.get('article_reference') != '') ?  a.get('article_reference') : a.get('page');
                    ref.push('<a style="margin: 0px 3px" data-view="' + view + '" data-annotation="' + a.get('id') + '" class="parent_annotation_link" href="' + link + '">' + article_reference + '</a>');
                });

                var text = a.get('page');
                    text += ' ('+ref.join(', ')+')';
                    page.push(text);
            });
            html += '<p style="padding: 5px 0px">';

            if (annotationGroupByPage.length > 1) {
                html += LANG.pages+ ': ';
            } else {
                html += LANG.page+': ';
            }

            html += page.join(', ');
            html += '</p>';
            $(el).html(html);
        }
    };

    return ParentAnnotation;

})(Annotator.Plugin);
