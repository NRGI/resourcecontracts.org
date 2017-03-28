Annotator.Plugin.Categories = (function (superClass) {
    __extends(Categories, superClass);

    Categories.prototype.options = {
        categories: []
    };

    Categories.prototype.pluginInit = function () {
        if (!Annotator.supported()) {
            return;
        }

        this.annotator.viewer.addField({
            load: this.updateViewer
        });

        this.field = this.annotator.editor.addField({
            id: 'annotation-plugin-select-category',
            type: 'select',
            options: this.options,
            load: this.updateCategory,
            submit: this.saveCategory
        });

        var self = this;
        $('.annotator-editor #annotation-language').on('change', function () {
            self.loadCategory($(this).val(), $(self.field).find('select').val());
        });
    };

    function Categories(element, options) {
        this.changeSelectedCategory = __bind(this.changeSelectedCategory, this);
        this.updateCategory = __bind(this.updateCategory, this);
        this.saveCategory = __bind(this.saveCategory, this);
        this.updateViewer = __bind(this.updateViewer, this);
        this.loadCategory = __bind(this.loadCategory, this);

        Categories.__super__.constructor.call(this, element, options);
        this.element = element;
    }

    Categories.prototype.getCategories = function (lang) {
        var collection = new AnnotationCategories();
        _.each(this.options.categories[lang], function (category, key) {
            collection.add({key: key, name: category});
        });
        return collection.invoke("pick", ["key", "name"])
    };

    Categories.prototype.updateViewer = function (field, annotation) {
        this.getCategories(CURRENT_LANG).map(function (cat) {
            if (cat.key == annotation.category) {
                $(field).addClass('annotator-category').html(cat.name);
            }
        });
    };

    Categories.prototype.saveCategory = function (el, annotation) {
        annotation.category = $(this.field).find('select option:selected').val();
    };

    Categories.prototype.updateCategory = function (event, annotation) {
        this.loadCategory('en', annotation.category);
    };

    Categories.prototype.loadCategory = function (lang, AnnotationCategory) {
        var category, categoryHTML, j, len, ref, totalWidth;

        categoryHTML = "<option value=''>" + LANG.select_category + "</option>";
        ref = this.getCategories(lang);

        var subHeaderPattern = new RegExp("^[0-9]+(-[a-zA-Z0-9-]+)");
        var headerPattern = new RegExp("^[i*]+(-[a-zA-Z0-9-]+)");

        for (var category in ref) {
            if (ref.hasOwnProperty(category)) {
                var obj = ref[category];
                var categoryClass = "val";
                var selectable = '';
                var withSpaces = "&nbsp&nbsp&nbsp&nbsp&nbsp" + obj.name;
                if (subHeaderPattern.test(obj.key)) {
                    selectable = 'disabled';
                    categoryClass = "sub-category";
                    withSpaces = "&nbsp&nbsp" + obj.name;
                }
                if (headerPattern.test(obj.key)) {
                    withSpaces = obj.name;
                    selectable = 'disabled';
                    categoryClass = "category";
                }

                var tmpDisableCategory = ['legal-enterprise-identifier'];
                if ($.inArray(obj.key, tmpDisableCategory) >= 0) {
                    selectable = 'disabled';
                }

                categoryHTML += '<option ' + selectable + ' class="' + this.options.categoryClass;
                categoryHTML += ' ' + categoryClass + '"';
                categoryHTML += ' value="' + obj.key + '">';
                categoryHTML += withSpaces;
                categoryHTML += '</option>';
            }
        }
        $(this.field).find('select').html(categoryHTML);
        if (!this.widthSet) {
            this.widthSet = true;
            totalWidth = 5;
            $("span.annotator-category").each(function (index) {
                totalWidth += parseInt($(this).outerWidth(true), 10);
            });
            $(".annotator-editor .annotator-widget").width(totalWidth);
        }
        return this.setSelectedCategory(AnnotationCategory);
    };

    Categories.prototype.setSelectedCategory = function (currentCategory) {
        $(this.field).find('.annotator-category').removeClass(this.options.classForSelectedCategory);
        if (currentCategory) $(this.field).find('select').val(currentCategory);
        $(this.field).find('select').select2({placeholder: LANG.select_category, allowClear: true, theme: "classic"});
        return $(this.field).find('.annotator-category:contains(' + currentCategory + ')').addClass(this.options.classForSelectedCategory);
    };

    return Categories;

})(Annotator.Plugin);