function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

var EditorCurrentPage = Backbone.Model.extend({    
    defaults: function() {
        return {
            id: 0,
            pageNumber: 1,
            text: 'Loading!',
        }
    },
    initialize: function(options) {
        this.options = options;
        this.totalPages = options.totalPages;
        this.eventsPipe = options.eventsPipe;
        this.eventsPipe.on("page-load", this.fetch, this);
        this.eventsPipe.on("searchresults-hightlight", this.setSearchTerm, this);
        this.set('pageNumber', this.options.pageNumber);
        return this;
    },
    fetch: function(page) {
        this.set("pageNumber", page);
        var self = this;
        if(this.xhr && this.xhr.readystate != 4){
            this.xhr.abort();
        }        
        this.xhr = $.ajax({
            url: self.options.loadUrl,
            data: 'page=' + page,
            type: 'GET',
            async: true,
            success: function(response) {
                self.set('id', response.id);
                self.set('text', nl2br(response.message));
                self.set('pdf_url', response.pdf);
                self.trigger('page-change');
                if (self.searchTerm) {
                    self.highLightText(self.searchTerm);
                }
            }
        });
        return this;
    },
    save: function(htmlContent) {
        $.ajax({
            url: this.options.saveUrl,
            data: {'text': this.get('text'), 'page': this.get('pageNumber')},
            type: 'POST'
        }).done(function (response) {
            this.textUpdated = false;
            $('#message').html('<div class="alert alert-success">'+response.message+'</div>');
            $('html,body').animate({ scrollTop: $('body').offset().top},'slow');
        });
    },    
    // setNextPage: function() {
    //     this.set("pageNumber", 1 + parseInt(this.get('pageNumber')));
    //     this.load(this.get("pageNumber"));
    //     this.eventsPipe.trigger('page-scroll-pagination')
    // },
    setSearchTerm: function(searchTerm) {
        this.searchTerm = searchTerm;
    },
    highLightText: function(searchTerm) {
        var regex = new RegExp(searchTerm, "gi");
        this.set('text', this.get('text').replace(regex, function(matched) {
            return "<i style='background-color: rgba(80,80,80,0.5);'>" + matched + "</i>";
        }));
    },
    setPage: function(page) {
        this.set("pageNumber", page);
        this.fetch(this.get("pageNumber"));
        this.eventsPipe.trigger('page-scroll-pagination')
    },
    getPage: function() {
        return this.get("pageNumber");
    },
    next: function() {
        if(this.get("pageNumber")+1 <= this.totalPages) {
            this.setPage(this.get("pageNumber")+1);
        }
    },
    previous: function() {
        if(this.get("pageNumber")-1 >= 1) {
            this.setPage(this.get("pageNumber")-1);
        }
    },
    getUrl: function() {
        return this.get('pdf_url');
    }
});

var TextEditorView = Backbone.View.extend({
    events: {
        'click #saveButton': 'save',
        'keydown .text-viewer': 'fixEnter'
    },
    initialize: function(options) {
        $('#saveButton').on('click', $.proxy(this.save, this));

        this.listenTo(this.model, "change:text", this.render);
        this.listenTo(this.model, "show-loading", this.showLoading);
        var self = this;
        this.editorEl = $(this.el).find('.text-viewer');
        if (this.model.get('isReadOnly')) {
            $('#saveButton').hide();
            this.editorEl.attr('contenteditable', 'false');
        } else {
            $('#saveButton').show();
            this.editorEl.attr('contenteditable', 'true');
        }
        return this;
    },
    fixEnter: function(e) {
        if (e.keyCode === 13) {
          // insert 2 br tags (if only one br tag is inserted the cursor won't go to the next line)
          document.execCommand('insertHTML', false, '<br>');
          // prevent the default behaviour of return key pressed
          return false;
      }
    },
    showLoading: function(page) {
        this.editorEl.html("Loading page "+page);
    },
    render: function() {
        this.editorEl.html(this.model.get('text'));
        return this;
    },
    getHtmlText: function() {
        return this.editorEl.html();
    },
    stripHTML: function(dirtyString) {
        //strip tags except for br
        var container = document.createElement('div');
        dirtyString = dirtyString.replace(/<br\s*[\/]?>/gi, "===br===")        
        container.innerHTML = dirtyString;
        text = container.textContent || container.innerText;
        return text.replace(/===br===/gi,"<br>");        
        // return text;
    },    
    save: function(e) {
        this.model.set('text', this.stripHTML(this.editorEl.html()));
        this.model.save();
        this.model.set('text', nl2br(this.editorEl.html()));
    }
});

var TextEditorPagination = Backbone.View.extend({
    tagName: 'div',
    events: {
        "click .next": "nextPage",
        "click .previous": "previousPage"
    },
    initialize: function(options) {
        this.currentPage = options.currentPage;
        this.rcEvents = options.rcEvents;
        this.currentPage.on('change:pageNumber', this.gotoPage, this);
        this.rcEvents.on('page-change', this.changePage, this);
        this.render();
    },
    changePage: function(page) {
        if(page) {
            this.currentPage.setPage(page);
        }
    },
    gotoPage: function() {
        $("#goto_page").val(this.currentPage.getPage());
    },
    render: function() {
        this.gotoPage();
    },
    nextPage: function(e) {
        e.preventDefault();
        this.currentPage.next();
        this.currentPage.trigger('show-loading', this.currentPage.getPage());
    },
    previousPage: function(e) {
        e.preventDefault();
        this.currentPage.previous();
        this.currentPage.trigger('show-loading', this.currentPage.getPage());
    }
});
