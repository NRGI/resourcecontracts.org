var Contract = Backbone.Model.extend({
    initialize: function(options) {
        this.options = options;
    },
    getTotalPages: function() {
        return this.options.totalPages;
    },
    canAnnotate: function() {
        return this.options.canAnnotate || false;
    },
    canEdit: function() {
        return this.options.canEdit || false;
    }
});