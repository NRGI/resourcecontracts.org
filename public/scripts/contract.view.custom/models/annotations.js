var Annotation = Backbone.Model.extend({
    default: {
        text: "",
        cluster: "Other12"

    }
});
var AnnotationsCollection = Backbone.Collection.extend({
    model: Annotation,
    sort_key: "category",
    parse: function(response) {
        return response.rows;
    },
    setSortByKey: function(key) {
        this.sort_key = key;
    },
    comparator: function(item) {
        return item.get(this.sort_key);
    },    
    parse: function(response) {
        return response.result;
    },
});
var AnnotationCategory = Backbone.Model.extend({});
var AnnotationCategories = Backbone.Collection.extend({
    model: AnnotationCategory
});
