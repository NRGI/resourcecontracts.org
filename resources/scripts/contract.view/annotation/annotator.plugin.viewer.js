Annotator.Plugin.AnnotatorNRGIViewer = (function (_super) {
    __extends(AnnotatorNRGIViewer, _super);
    AnnotatorNRGIViewer.prototype.field = null;
    AnnotatorNRGIViewer.prototype.input = null;
    AnnotatorNRGIViewer.prototype.pluginInit = function (options) {
        var annotator = this.annotator;
        if (!Annotator.supported()) {
            return;
        }

        $('.annotator-controls').on("click", "a.annotator-save", function (e) {
            var wrapperEl = $('.' + contractApp.getView() + '-annotator');
            var category = wrapperEl.find('#annotation-plugin-select-category');
            var text = wrapperEl.find('#text_en');
            wrapperEl.find('.error').remove();
            if (category.val() == '') {
                category.focus();
                category.parent().append('<span class="error">' + LANG.category_required + '</span>');
                e.stopPropagation();
                return;
            }
        });

        annotator.viewer.addField({
            load: this.updateViewer,
        });
    };
    function AnnotatorNRGIViewer(element, options) {
        AnnotatorNRGIViewer.__super__.constructor.apply(this, arguments);
    };
    AnnotatorNRGIViewer.prototype.onClickAnnotionMore = function d(e, annotation) {
        e.preventDefault();
        AnnotatorNRGIViewer.contractApp.trigger("annotations:highlight", obj.annotation);
    };
    AnnotatorNRGIViewer.prototype.updateViewer = function (field, annotation) {
        var link = "";
        if (annotation.shapes) {
            link = "#/pdf/page/" + annotation.page_no + "/annotation/" + annotation.id;
        } else {
            link = "#/text/page/" + annotation.page_no + "/annotation/" + annotation.id;
        }
        var textDiv = $(field.parentNode).find('div:first-of-type')[0];
        var text = '';
        var annotatedText = annotation.text;
        if (parseInt(annotation.parent) > 0) {
            var parentAnnotation = annotationsCollection.get(annotation.parent);
            if (parentAnnotation) {
                annotatedText = parentAnnotation.get('text');
            }
        }

        if (typeof annotatedText == 'undefined') {
            return false;
        }
        if (annotatedText != '') {
            text = annotatedText.split(" ").splice(0, 10).join(" ");
            text = nl2br(text);
            if (annotatedText.split(" ").length > 10) {
                text = text + " ...";
            }
        }

        var article_reference = '';
        if (typeof annotation.article_reference !== 'undefined' && annotation.article_reference != '') {
            article_reference = ' - ' + annotation.article_reference;
        }

        textDiv.innerHTML = '<div class="annotation-viewer-text">' +
            text + article_reference + '<a href=' + link + '> >>' + '</a></div>';

        $(textDiv).on("click", "a", function (e) {
            e.preventDefault();
            contractApp.trigger("annotations:highlight", annotation);
        });

        $('.annotator-controls').on("click", "button.annotator-delete", function (e) {
            var deleteThis = confirm(LANG.confirm_annotation_delete);
            if (deleteThis === true) {
                return;
            }
            e.stopPropagation();
        });

        $(field).remove();
    };

    return AnnotatorNRGIViewer;
})(Annotator.Plugin);