Annotator.Plugin.AnnotatorNRGIViewer = (function (_super) {
    __extends(AnnotatorNRGIViewer, _super);
    AnnotatorNRGIViewer.prototype.field = null;
    AnnotatorNRGIViewer.prototype.input = null;
    AnnotatorNRGIViewer.prototype.pluginInit = function (options) {
        var annotator = this.annotator;
        if (!Annotator.supported()) {
            return;
        }
        annotator.viewer.addField({
            load: this.updateViewer,
        });
    };
    function AnnotatorNRGIViewer(element, options) {
        AnnotatorNRGIViewer.__super__.constructor.apply(this, arguments);
    };
    AnnotatorNRGIViewer.prototype.onClickAnnotionMore = function (e, annotation) {
        e.preventDefault();
        AnnotatorNRGIViewer.contractApp.trigger("annotations:highlight", obj.annotation);
    },
        AnnotatorNRGIViewer.prototype.updateViewer = function (field, annotation) {
            var link = "";
            if (annotation.shapes) {
                link = "#/pdf/page/" + annotation.page_no + "/annotation/" + annotation.id;
            } else {
                link = "#/text/page/" + annotation.page_no + "/annotation/" + annotation.id;
            }
            var textDiv = $(field.parentNode).find('div:first-of-type')[0];
            var text = annotation.text.split(" ").splice(0, 10).join(" ");
            if (annotation.text.split(" ").length > 10) {
                text = text + " ...";
            }

            var section = '';
            if (typeof annotation.section !== 'undefined') {
                section = ' - ' + annotation.section;
            }

            textDiv.innerHTML = '<div class="annotation-viewer-text">' +
                text + section + '</div>' +
                '<p>Page ' + annotation.page +
                '<a href="#' + "" + '" class="annotation-viewer-more"> >> </a>' +
                '</p>';

            $(textDiv).find('.annotation-viewer-text').data('annotation',annotation);

            $(textDiv).on("click", "a", function (e) {
                e.preventDefault();
                contractApp.trigger("annotations:highlight", annotation);
            });

            $('.annotator-controls').on("click", "button.annotator-delete", function (e) {
                var deleteThis = confirm("You sure to delete this annotation?");
                if (deleteThis === true) {
                    return;
                }
                e.stopPropagation();
            });
            // $(textDiv).find("a").onclick = function() { console.log('here')};
            $(field).remove(); //this is the auto create field by annotator and it is not necessary
        }

    return AnnotatorNRGIViewer;
})(Annotator.Plugin);