Annotator.Plugin.ArticleReference = (function (_super) {
    __extends(ArticleReference, _super);

    ArticleReference.prototype.pluginInit = function (options) {
        if (!Annotator.supported()) {
            return;
        }

       var field =  this.annotator.editor.addField({
            label: LANG.article_reference,
            type: 'input',
            id:'article_reference',
            load: this.updateArticleReference,
            submit: this.saveArticleReference
        });

        $('.annotator-wrapper').find('input, textarea').attr('spellcheck', 'true');
    };

    function ArticleReference() {
        ArticleReference.__super__.constructor.call(this, arguments);
    }

    ArticleReference.prototype.saveArticleReference = function (el, annotation) {
        annotation.article_reference = $(el).find('input').val();
    }

    ArticleReference.prototype.updateArticleReference = function (el, annotation) {
        $(el).find('input').val(annotation.article_reference);
    };

    return ArticleReference;
})(Annotator.Plugin);
