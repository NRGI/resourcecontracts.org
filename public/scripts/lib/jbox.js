    $.widget("ui.boxer", $.ui.mouse, {

    start: function () {
        this._init();

    },
    _init: function () {
        this.element.addClass("ui-boxer");
        this.dragged = true;

        this._mouseInit();

        this.helper = $(document.createElement('div'))
            .css({border: '1px dotted black'})
            .addClass("ui-boxer-helper");
    },
    destroy: function () {
          this.element
         .removeClass("ui-boxer ui-boxer-disabled")
         .removeData("boxer")
         .unbind(".boxer");
        this._mouseDestroy();
        return this;
    },

    getActualPos:function(event)
    {
       var posX = $('canvas').offset().left,
           posY = $('canvas').offset().top;
        event.pageX = event.pageX - posX;
        event.pageY = event.pageY - posY;

       return event;
    },

    _mouseStart: function (event) {

        var self = this

        event = this.getActualPos(event);

        if (this.options.disabled)
            return;

        this._trigger("preStart", event);

        this.opos = [event.pageX, event.pageY];

        var options = this.options;

        this._trigger("start", event);

        $(options.appendTo).append(this.helper);
        this.helper.css({
            "z-index": 100,
            "position": "absolute",
            "left": event.pageX,
            "top": event.pageY,
            "width": 0,
            "height": 0
        }).addClass('boxer-hl').addClass('ui-boxer-helper');

    },

    _mouseDrag: function (event) {
        var self = this;
        this.dragged = true;

        event = this.getActualPos(event);


        if (this.options.disabled)
            return;

        var options = this.options;

        var x1 = this.opos[0], y1 = this.opos[1], x2 = event.pageX, y2 = event.pageY;

        if (x1 > x2) {
            var tmp = x2;
            x2 = x1;
            x1 = tmp;
        }
        if (y1 > y2) {
            var tmp = y2;
            y2 = y1;
            y1 = tmp;
        }

        this.helper.css({left: x1, top: y1, width: x2 - x1, height: y2 - y1});

        $('.boxer-hl').remove();

        var clone = this.helper.clone()
            .removeClass('ui-boxer-helper').appendTo(this.element);

        this._trigger("drag", event);

        return false;

    },

    _mouseStop: function (event) {
        var self = this;

        this.dragged = false;

        var options = this.options;

        if(options.disabled)
        return;

        $('.boxer-hl').remove();
        var clone = this.helper.clone()
            .removeClass('boxer-hl').removeClass('ui-boxer-helper').appendTo(this.element);

        this._trigger("stop", event, {box: clone});

        this.helper.remove();

        return false;
    }

});

$.extend($.ui.boxer, {
    defaults: $.extend({}, $.ui.mouse.defaults, {
        appendTo: '#canvas',
        distance: 0
    })
});
