Annotator.Plugin.ParentAnnotation = (function (_super) {
    __extends(ParentAnnotation, _super);

    ParentAnnotation.prototype.pluginInit = function (options) {
        if (!Annotator.supported()) {
            return;
        }

        this.annotator.editor.addField({
            label: 'Parent Annotation',
            type: 'select',
            load: this.updateParentAnnotation,
            submit: this.saveParentAnnotation
        });
    };

    function ParentAnnotation() {
        ParentAnnotation.__super__.constructor.call(this, arguments);
    }

    ParentAnnotation.prototype.saveParentAnnotation = function (el, annotation) {
        annotation.parent = $(el).find('select').val();
    };

    ParentAnnotation.prototype.updateParentAnnotation = function (el, annotation) {
        var select = '<option value="">Select parent annotation</option>';
        var selected ='';
        annotationsCollection.models.map(function (a) {
            selected = (annotation.parent == a.get('id')) ? 'selected="selected"' : '';
            select += '<option value="' + a.get('id') + '" '+selected+'>' + a.get('text') + '</option>';
        });

        $(el).find('select').html(select);
        $(el).find('select').select2({placeholder: 'Select parent annotation'});
    };

    return ParentAnnotation;

})(Annotator.Plugin);
