Annotator.Plugin.ArticleReference = (function (_super) {
    __extends(ArticleReference, _super);

    ArticleReference.prototype.options = {
        lang: TRANSLATION_LANG
    };

    ArticleReference.prototype.pluginInit = function (options) {
        if (!Annotator.supported()) {
            return;
        }
        var lang = this.options.lang;
        var self = this;

        $.each(lang, function (i, l) {
            self.annotator.editor.addField({
                label: LANG.article_reference + ' in ' + l.name,
                type: 'input',
                id: 'article_reference_' + l.code,
                load: function (el, annotation) {
                    self.updateArticleReference(l.code, el, annotation);
                },
                submit: function (el, annotation) {
                    self.saveArticleReference(l.code, el, annotation)
                }
            });
        });


        $.each(lang, function (i, l) {
            self.annotator.editor.addField({
                label: 'Annotation in ' + l.name,
                type: 'textarea',
                id: 'text_' + l.code,
                load: function (el, annotation) {
                    self.updateText(l.code, el, annotation);
                },
                submit: function (el, annotation) {
                    self.saveText(l.code, el, annotation)
                }
            });
        });

        $('.annotator-wrapper').find('input, textarea').attr({'spellcheck': 'true', 'autocomplete': 'off'});
        this.annotator.subscribe("annotationEditorShown", onEditorShownHandler)
    };

    function ArticleReference() {
        ArticleReference.__super__.constructor.call(this, arguments);
    }

    ArticleReference.prototype.saveArticleReference = function (code, el, annotation) {
        annotation['article_reference_' + code] = $(el).find('input').val();
    };

    ArticleReference.prototype.updateArticleReference = function (code, el, annotation) {
        var value = annotation.article_reference;
        if (code != 'en') {
            value = (annotation.article_reference_locale && typeof annotation.article_reference_locale[code] != 'undefined') ? annotation.article_reference_locale[code] : '';
        }

        $(el).find('input').addClass('article_reference').val(value);

        if (code != 'en') {
            $(el).hide();
        } else {
            $(el).show();
        }

    };

    ArticleReference.prototype.saveText = function (code, el, annotation) {
        annotation['text_' + code] = $(el).find('textarea').val();
    };

    ArticleReference.prototype.updateText = function (code, el, annotation) {
        var value = annotation.text;

        if (code != 'en') {
            value = (annotation.text_locale && typeof annotation.text_locale[code] != 'undefined') ? annotation.text_locale[code] : '';
        }

        $(el).find('textarea').addClass('text').val(value);

        if (code != 'en') {
            $(el).hide();
        } else {
            $(el).show();
        }
    };

    function onEditorShownHandler(el) {
        var viewerEl = $(el.element);
        viewerEl.find('.annotator-listing li textarea#annotator-field-10').parent().remove();
    }

    return ArticleReference;
})(Annotator.Plugin);
