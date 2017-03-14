var Annotation = Backbone.Model.extend({
    default: {
        text: "",
        cluster: "Other12"

    }
});
var AnnotationsCollection = Backbone.Collection.extend({
    model: Annotation,
    sort_key: "category",
    setSortByKey: function (key) {
        this.sort_key = key;
    },
    comparator: function (item) {
        return item.get(this.sort_key);
    },
    parse: function (response) {
        return response.result;
    },
    parentAnnotations: function (category) {
        var parents = [];
        this.models.map(function (a) {
            if (a.get('category_key') == category) {
                parents[a.get('annotation_id')] = a.get('text');
            }
        });
        return parents;
    },
    relatedAnnotations: function (annotation) {
        var child = [];
        this.models.map(function (a) {
            if (a.get('annotation_id') == annotation.annotation_id && a.get('id') != annotation.id) {
                child.push(a);
            }
        });

        return child;
    },
    groupByCategory: function () {
        var annotations = this.groupBy(function (model) {
            return model.get('category_key');
        });

        return annotations;
    },
    groupByPage: function () {
        var annotations = this.groupBy(function (model) {
            return model.get('page');
        });

        return annotations;
    },
    totalAnnotations: function () {
        return _.keys(this.groupByCategory()).length;
    }
});
var AnnotationCategory = Backbone.Model.extend({});
var AnnotationCategories = Backbone.Collection.extend({
    model: AnnotationCategory
});

var AnnotationChecklist = Backbone.Model.extend({});
var AnnotationChecklistCollection = Backbone.Collection.extend({
    model: AnnotationChecklist
});
