Annotator.Plugin.Section = (function (_super) {
    __extends(Section, _super);

    Section.prototype.pluginInit = function (options) {
        if (!Annotator.supported()) {
            return;
        }

       var field =  this.annotator.editor.addField({
            label: 'Section',
            type: 'input',
            id:'section',
           spellcheck :'true',
            load: this.updateSection,
            submit: this.saveSection
        });

        $('input, textarea').attr('spellcheck', 'true');
    };

    function Section() {
        Section.__super__.constructor.call(this, arguments);
    }

    Section.prototype.saveSection = function (el, annotation) {
        annotation.section = $(el).find('input').val();
    }

    Section.prototype.updateSection = function (el, annotation) {
        $(el).find('input').val(annotation.section);
    };

    return Section;
})(Annotator.Plugin);
