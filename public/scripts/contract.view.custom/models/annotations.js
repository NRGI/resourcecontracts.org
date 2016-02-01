var Annotation = Backbone.Model.extend({
    default: {
        text: "",
        cluster: "Other12"

    }
});
var AnnotationsCollection = Backbone.Collection.extend({
    model: Annotation,
    sort_key: "category",
    parse: function (response) {
        return response.rows;
    },
    setSortByKey: function (key) {
        this.sort_key = key;
    },
    comparator: function (item) {
        return item.get(this.sort_key);
    },
    parse: function (response) {
        return response.result;
    },
    parentAnnotations: function (annotation) {
        var parents = [];
        this.models.map(function (a) {
            if (a.get('category_key') == annotation.category && a.get('parent') == '') {
                if (annotation.id == null) {
                    parents.push(a);
                }
                else if (a.get('id') != annotation.id) {
                    parents.push(a);
                }
            }
        });
        return parents;
    },
    childAnnotations: function (annotation) {
        var child = [];
        this.models.map(function (a) {
            if (a.get('parent') == annotation.id) {
                child.push(a);
            }
        });

        return child;
    },
    relatedAnnotations: function (annotation) {
        var related = [];
        var self = this;
        this.models.map(function (parent) {
            if (parent.get('id') == annotation.parent) {
                related.push(parent);
                self.models.map(function (child) {
                    if (child.get('parent') == parent.id && child.id != annotation.id) {
                        related.push(child);
                    }
                });
            }
        });
        return related;
    }
});
var AnnotationCategory = Backbone.Model.extend({});
var AnnotationCategories = Backbone.Collection.extend({
    model: AnnotationCategory
});
