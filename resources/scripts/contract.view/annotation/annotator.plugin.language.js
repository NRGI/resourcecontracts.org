Annotator.Plugin.Language = (function (_super) {
    __extends(Language, _super);

    Language.prototype.pluginInit = function (options) {
        if (!Annotator.supported()) {
            return;
        }
        this.field = this.annotator.editor.addField({
            label: 'Language',
            type: 'select',
            id: 'annotation-language',
            load: this.updateLanguage,
            submit: this.saveLanguage
        });
    };

    function Language() {
        Language.__super__.constructor.call(this, arguments);
    }

    Language.prototype.saveLanguage = function (el, annotation) {
        annotation.language = $(el).find('input').val();
    };

    Language.prototype.updateLanguage = function (el, annotation) {
        var lang = {'en': 'English', 'fr': 'French', 'ar': 'Arabic'};
        var options = '';
        $.each(lang, function (code, name) {
            options += '<option value="' + code + '">' + name + '</option>';
        });

        $(el).find('select').html(options);
    };

    return Language;
})(Annotator.Plugin);
