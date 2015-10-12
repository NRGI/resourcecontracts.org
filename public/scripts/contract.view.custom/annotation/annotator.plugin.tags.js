Annotator.Plugin.MyTags = (function(_super) {
      __extends(MyTags, _super);

      function MyTags() {
          this.setAnnotationMyTags = __bind(this.setAnnotationMyTags, this);
          this.updateField = __bind(this.updateField, this);
          return MyTags.__super__.constructor.apply(this, arguments);
      }
      MyTags.prototype.options = {
          availableTags: [],
      };
      MyTags.prototype.events = {
          'annotationEditorShown': "updateField"
      };
      MyTags.prototype.field = null;
      MyTags.prototype.input = null;
      MyTags.prototype.pluginInit = function() {
          if (!Annotator.supported()) {
              return;
          }
          this.field = this.annotator.editor.addField({
              submit: this.setAnnotationMyTags
          });
          this.annotator.viewer.addField({
              load: this.updateViewer
          });
          return this.input = $(this.field).find(':input');
      };
      MyTags.prototype.updateField = function(field, annotation) {
          var allTags = _.union(annotation.tags, this.availableTags);
          tagHTML = "<select id='annotator-tags-select' multiple>";
          for (j = 0, len = allTags.length; j < len; j++) {
              var tag = allTags[j];
              tagHTML += '<option val="' + tag + '" ';
              if (_.indexOf(annotation.tags, tag) !== -1) {
                  tagHTML += ' selected="selected" '
              }
              tagHTML += ' >';
              tagHTML += tag;
              tagHTML += '</option>';
          }
          tagHTML += '</select>';
          $(this.field).html(tagHTML);
          $(this.field).find("#annotator-tags-select").select2({
              multiple: true,
              tags: true,
              placeholder: 'choose multiple tags'
          });
          return this.input;
      };
      MyTags.prototype.setAnnotationMyTags = function(field, annotation) {
          var tags = [];
          $(this.field).find('select :selected').each(function(i, selected) {
              tags[i] = $(selected).text();
          });
          return annotation.tags = tags;
      };
      MyTags.prototype.updateViewer = function(field, annotation) {
          field = $(field);
          if (annotation.tags && $.isArray(annotation.tags) && annotation.tags.length) {
              return field.addClass('annotator-tags').html(function() {
                  var string;
                  return string = $.map(annotation.tags, function(tag) {
                      return '<span class="annotator-tag">' + Annotator.Util.escape(tag) + '</span>';
                  }).join(' ');
              });
          } else {
              return field.remove();
          }
      };
      return MyTags;
  })(Annotator.Plugin);