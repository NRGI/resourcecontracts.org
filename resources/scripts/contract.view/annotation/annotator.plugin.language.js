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
            load: this.updateLanguage
        });
    };

    function Language(element, options) {
        Language.__super__.constructor.call(this, element, options);
    }

    Language.prototype.updateLanguage = function (el, annotation) {
        var options = '';
        var lang = getLang();

        $.each(lang, function (k, l) {
            options += '<option value="' + l.code + '">' + l.name + '</option>';
        });

        $(el).find('select').html(options).select2();

        $(el).find('select').on('change', function () {
            $('.annotator-widget .article_reference').each(function () {
                $(this).parent().hide();
            });

            $('.annotator-widget #article_reference_' + $(this).val()).parent().show();

            $('.annotator-widget .text').each(function () {
                $(this).parent().hide();
            });

            $('.annotator-widget #text_' + $(this).val()).parent().show();
        });
    };

    function getLang() {
        return TRANSLATION_LANG;
    }

    return Language;
})(Annotator.Plugin);
