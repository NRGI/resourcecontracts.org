// Boxer plugin
$.widget("ui.boxer", $.extend({}, $.ui.mouse, {


    start:function(){
        console.log('restart');
        this._init();

    },
    _init: function () {
        this.element.addClass("ui-boxer");
        this.dragged = true;
        console.dir(this);

        this._mouseInit();

        this.helper = $(document.createElement('div'))
            .css({border: '1px dotted black'})
            .addClass("ui-boxer-helper");
        console.log('init');

    },

    destroy: function () {
      /*  this.element
            .removeClass("ui-boxer ui-boxer-disabled")
            .removeData("boxer")
            .unbind(".boxer");*/
        this._mouseDestroy();
        console.log('destroy');
        return this;
    },

    _mouseStart: function (event) {

        var self = this
        this._trigger("preStart", event);

        this.opos = [event.pageX, event.pageY];
        console.log([event.pageX, event.pageY]);

        if (this.options.disabled)
            return;

        var options = this.options;

        this._trigger("start", event);

        $(options.appendTo).append(this.helper);

        this.helper.css({
            "z-index": 100,
            "position": "absolute",
            "left": event.clientX,
            "top": event.clientY,
            "width": 0,
            "height": 0
        });
        console.log('start')

    },

    _mouseDrag: function (event) {
        var self = this;
        this.dragged = true;

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
        this.helper.css({left: x1-365, top: y1, width: x2 - x1, height: y2 - y1});

        this._trigger("drag", event);

        console.log('drag');
        return false;

    },

    _mouseStop: function (event) {
        var self = this;

        this.dragged = false;

        var options = this.options;

        var clone = this.helper.clone()
            .removeClass('ui-boxer-helper').appendTo(this.element);

        this._trigger("stop", event, {box: clone});

        this.helper.remove();
        console.log('stop');

        return false;
    }

}));
$.extend($.ui.boxer, {
    defaults: $.extend({}, $.ui.mouse.defaults, {
        appendTo: '#canvas',
        distance: 0
    })
});



$(document).ready(function () {
    canvas_width_multiplier = window.innerHeight / window.innerWidth;
    $('#canvas').height($('#canvas').width() * canvas_width_multiplier);
    function show_pop(offset, ui) {
        var html = $(document.createElement('div'))
            .addClass("box-pop")
            .css({top:ui.height()+10, left:-ui.width()/2})
            .html('<a href="#" class="btn-yes btn btn-primary">Yes</a> <a href="#" class="btn-no btn btn-danger">No</a');
            ui.append(html);
           $('#canvas').boxer('destroy');
    }

    $(document).on( 'click', '.btn-yes', function(){
        $('#canvas').boxer('start');
        $(this).parent().remove();
    });

    $(document).on('click','.btn-no', function(){
        $('#canvas').boxer('start');
        $(this).parent().parent().remove();
    });

    // Using the boxer plugin
    var data = $('#canvas').boxer({
        stop: function (event, ui) {
            var offset = ui.box.offset();
            ui.box.css({border: '1px solid black'})
            show_pop(offset, ui.box);
        }
    });


});
