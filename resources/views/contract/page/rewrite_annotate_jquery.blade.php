@extends('layout.app-full')

@section('script')
    <script src="//code.jquery.com/jquery-migrate-1.2.1.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
    <script>
    $(document).ready(function() {
        canvas_width_multiplier = window.innerHeight / window.innerWidth;
        $('#canvas').height($('#canvas').width() * canvas_width_multiplier);
    });

    // Boxer plugin
    $.widget("ui.boxer", $.extend({}, $.ui.mouse, {

        _init: function() {
            this.element.addClass("ui-boxer");

            this.dragged = true;
            console.dir(this);

            this._mouseInit();

            this.helper = $(document.createElement('div'))
                    .css({border:'1px dotted black'})
                    .addClass("ui-boxer-helper");
        },

        destroy: function() {
            this.element
                    .removeClass("ui-boxer ui-boxer-disabled")
                    .removeData("boxer")
                    .unbind(".boxer");
            this._mouseDestroy();

            return this;
        },

        _mouseStart: function(event) {
            var self = this;

            this.opos = [event.pageX, event.pageY];

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
        },

        _mouseDrag: function(event) {
            var self = this;
            this.dragged = true;

            if (this.options.disabled)
                return;

            var options = this.options;

            var x1 = this.opos[0], y1 = this.opos[1], x2 = event.pageX, y2 = event.pageY;
            if (x1 > x2) { var tmp = x2; x2 = x1; x1 = tmp; }
            if (y1 > y2) { var tmp = y2; y2 = y1; y1 = tmp; }
            this.helper.css({left: x1, top: y1, width: x2-x1, height: y2-y1});

            this._trigger("drag", event);

            return false;
        },

        _mouseStop: function(event) {
            var self = this;

            this.dragged = false;

            var options = this.options;

            var clone = this.helper.clone()
                    .removeClass('ui-boxer-helper').appendTo(this.element);

            this._trigger("stop", event, { box: clone });

            this.helper.remove();

            return false;
        }

    }));
    $.extend($.ui.boxer, {
        defaults: $.extend({}, $.ui.mouse.defaults, {
            appendTo: 'body',
            distance: 0
        })
    });

    // Using the boxer plugin
    $('#canvas').boxer({
        stop: function(event, ui) {
            var offset = ui.box.offset();
            ui.box.css({ border: '1px solid black'})
        }
    });

</script>

    <script src="{{asset('annotation/pdf.js')}}"></script>

    <script id="script">
        $(function(){

            //
            // If absolute URL from the remote server is provided, configure the CORS
            // header on that server.
            //
            var url = 'https://rc-stage.s3-us-west-2.amazonaws.com/1464/1464-peru-yanac-minera-ministerio-minas-exploration-investment-contract-2014.pdf';


            //
            // Disable workers to avoid yet another cross-origin issue (workers need
            // the URL of the script to be loaded, and dynamically loading a cross-origin
            // script does not work).
            //
             PDFJS.disableWorker = true;

            //
            // In cases when the pdf.worker.js is located at the different folder than the
            // pdf.js's one, or the pdf.js is executed via eval(), the workerSrc property
            // shall be specified.
            //
            // PDFJS.workerSrc = '../../build/pdf.worker.js';

            var pdfDoc = null,
                    pageNum = 1,
                    pageRendering = false,
                    pageNumPending = null,
                    scale =2,
                    canvas = document.getElementById('the-canvas'),
                    ctx = canvas.getContext('2d');

            /**
             * Get page info from document, resize canvas accordingly, and render page.
             * @param num Page number.
             */
            function renderPage(num) {
                pageRendering = true;
                // Using promise to fetch the page
                pdfDoc.getPage(num).then(function(page) {
                    var viewport = page.getViewport(scale);
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    var renderTask = page.render(renderContext);

                    // Wait for rendering to finish
                    renderTask.promise.then(function () {
                        pageRendering = false;
                        if (pageNumPending !== null) {
                            // New page rendering is pending
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });

                // Update page counters
                document.getElementById('page_num').textContent = pageNum;
            }

            /**
             * If another page rendering in progress, waits until the rendering is
             * finised. Otherwise, executes rendering immediately.
             */
            function queueRenderPage(num) {
                if (pageRendering) {
                    pageNumPending = num;
                } else {
                    renderPage(num);
                }
            }

            /**
             * Displays previous page.
             */
            function onPrevPage() {
                if (pageNum <= 1) {
                    return;
                }
                pageNum--;
                queueRenderPage(pageNum);
            }
            document.getElementById('prev').addEventListener('click', onPrevPage);

            /**
             * Displays next page.
             */
            function onNextPage() {
                if (pageNum >= pdfDoc.numPages) {
                    return;
                }
                pageNum++;
                queueRenderPage(pageNum);
            }
            document.getElementById('next').addEventListener('click', onNextPage);

            /**
             * Asynchronously downloads PDF.
             */
            PDFJS.getDocument(url).then(function (pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('page_count').textContent = pdfDoc.numPages;

                // Initial/first page rendering
                renderPage(pageNum);
            });


            $('.zoom').on('click',function()
            {
                scale = $(this).data('scale');
                renderPage(2)
            });

        })

    </script>
@stop

@section('content')
    <div>
        <button id="prev">Previous</button>
        <button id="next">Next</button>
        <button class="zoom" data-scale="0.5">Zoom 50%</button>
        <button class="zoom" data-scale="1.0">Zoom 100%</button>
        <button class="zoom" data-scale="1.5">Zoom 150%</button>
        <button class="zoom" data-scale="2.0">Zoom 200%</button>

        &nbsp; &nbsp;
        <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
    </div>

    <div id="canvas">
        <canvas id="the-canvas" style="border:1px solid black;"></canvas>
    </div>
@stop