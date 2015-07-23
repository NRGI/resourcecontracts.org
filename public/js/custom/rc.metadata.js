var MetadataView = Backbone.View.extend({
    events: {
        'click': 'render'
    },
    initialize: function(options) {
        this.options = options;
    },
    render: function() {
        var that = this;
        var template = _.template($('#metadata-view-template').html());
        this.$el.append(template(this.options.metadata));
        return this;
    },
    toggle: function() {
        $(this.el).toggle();
    }
});
var MetadataButtonView = Backbone.View.extend({
    events: {
        'click': 'toggle'
    },
    initialize: function(options) {
        this.metadataView = options.metadataView;
        return this;
    },
    toggle: function(e) {
        e.preventDefault();
        this.metadataView.toggle();
    },
});