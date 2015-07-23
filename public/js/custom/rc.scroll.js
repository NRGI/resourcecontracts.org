//http://jsfiddle.net/QN3mK/
function Scroller(options) {
    this.currentPageTop = function() {
        return this.f_filterResults(window.pageYOffset ? window.pageYOffset : 0, document.documentElement ? document.documentElement.scrollTop : 0, document.body ? document.body.scrollTop : 0);
    };
    this.paginationTop = function() {
        return this.currentPageTop() + $("#pagination")[0].getBoundingClientRect()["top"];
    };
    this.f_filterResults = function(n_win, n_docel, n_body) {
        var n_result = n_win ? n_win : 0;
        if (n_docel && (!n_result || (n_result > n_docel))) n_result = n_docel;
        return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
    };
    this.scrollPageToTop = function() {
        var currentPageTop = this.currentPageTop();
        if ($(this.editorEl).first()) {
            if ($(this.editorEl).first().find('div')[0]) {
                $(this.editorEl).first().find('div')[0].scrollIntoView();
                window.scrollTo(0, currentPageTop);
            }
        }
    };
    this.scrollPageToPagination = function() {
        if ($(this.editorEl).first()) {
            if ($(this.editorEl).first().find('div')[0]) {
                $(this.editorEl).first().find('div')[0].scrollIntoView();
                window.scrollTo(0, this.paginationTop());
            }
        }
    };
    this.init = function(options) {
        this.eventsPipe = options.eventsPipe;
        this.eventsPipe.on("page-scroll", this.scrollPageToTop, this);
        this.eventsPipe.on("page-scroll-pagination", this.scrollPageToPagination, this);
        this.editorEl = options.editorEl;
    };
    this.init(options);
    return;
}