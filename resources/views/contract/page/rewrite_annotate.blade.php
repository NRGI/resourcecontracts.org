@extends('layout.app-full')

@section('script')
    <?php
    $list =[
        'core',
        'classapplier',
        'highlighter',
        'selectionsaverestore',
        'serializer'
    ];
    ?>
    @foreach($list as $s)
    <script src="{{asset(sprintf('annotation/rangy-%s.js',$s))}}"></script>
    @endforeach

<script>
    var highlighter;

    $(function(){
        rangy.init();
        highlighter = rangy.createHighlighter();

        highlighter.addClassApplier(rangy.createClassApplier("note", {
            ignoreWhiteSpace: true,
            elementTagName: "a",
            elementProperties: {
                href: "#",
                id:'ssss',
                onclick: function () {
                    var highlight = highlighter.getHighlightForElement(this);
                    if (window.confirm("Delete this note (ID " + highlight.id + ")?")) {
                        highlighter.removeHighlights([highlight]);
                    }
                    return false;
                }
            }
        }));

        $(document).on('mouseup', 'div',function() {
            var text = rangy.getSelection().toString().trim();
            var save = saveSelection(document.getElementById('summary'));
            $('.input').val(JSON.stringify(save));
            if (text != '') {
                highlighter.highlightSelection("note",{containerElementId: "summary"});
            }
        });

        function saveSelection(containerEl) {
            var charIndex = 0, start = 0, end = 0, foundStart = false, stop = {};
            var sel = rangy.getSelection(), range;

            function traverseTextNodes(node, range) {
                if (node.nodeType == 3) {
                    if (!foundStart && node == range.startContainer) {
                        start = charIndex + range.startOffset;
                        foundStart = true;
                    }
                    if (foundStart && node == range.endContainer) {
                        end = charIndex + range.endOffset;
                        throw stop;
                    }
                    charIndex += node.length;
                } else {
                    for (var i = 0, len = node.childNodes.length; i < len; ++i) {
                        traverseTextNodes(node.childNodes[i], range);
                    }
                }
            }

            if (sel.rangeCount) {
                try {
                    traverseTextNodes(containerEl, sel.getRangeAt(0));
                } catch (ex) {
                    if (ex != stop) {
                        throw ex;
                    }
                }
            }

            return {
                start: start,
                end: end
            };
        }

        function restoreSelection(containerEl, savedSel) {
            var charIndex = 0, range = rangy.createRange(), foundStart = false, stop = {};
            range.collapseToPoint(containerEl, 0);

            function traverseTextNodes(node) {
                if (node.nodeType == 3) {
                    var nextCharIndex = charIndex + node.length;
                    if (!foundStart && savedSel.start >= charIndex && savedSel.start <= nextCharIndex) {
                        range.setStart(node, savedSel.start - charIndex);
                        foundStart = true;
                    }
                    if (foundStart && savedSel.end >= charIndex && savedSel.end <= nextCharIndex) {
                        range.setEnd(node, savedSel.end - charIndex);
                        throw stop;
                    }
                    charIndex = nextCharIndex;
                } else {
                    for (var i = 0, len = node.childNodes.length; i < len; ++i) {
                        traverseTextNodes(node.childNodes[i]);
                    }
                }
            }

            try {
                traverseTextNodes(containerEl);
            } catch (ex) {
                if (ex == stop) {
                    rangy.getSelection().setSingleRange(range);
                } else {
                    throw ex;
                }
            }
        }

        function deserializeSelection()
        {
            restoreSelection(document.getElementById('summary'), JSON.parse($('.input').val()));
            highlighter.highlightSelection("note",{containerElementId: "summary"});

            restoreSelection(document.getElementById('summary'), JSON.parse('{"start":374,"end":382}'));
            highlighter.highlightSelection("note",{containerElementId: "summary"});

            restoreSelection(document.getElementById('summary'), JSON.parse('{"start":722,"end":772}'));
            highlighter.highlightSelection("note",{containerElementId: "summary"});

            restoreSelection(document.getElementById('summary'), JSON.parse('{"start":722,"end":772}'));
            highlighter.highlightSelection("note",{containerElementId: "summary"});



            window.getSelection().removeAllRanges();
        }
        $('.btn').on('click', deserializeSelection);

    });



</script>

    <script src="{{asset('annotation/pdf.js')}}"></script>

    <script id="script">
        $(function(){

            //
        // If absolute URL from the remote server is provided, configure the CORS
        // header on that server.
        //
        var url = '{{asset('annotation/compressed.tracemonkey-pldi-09.pdf')}}';


        //
        // Disable workers to avoid yet another cross-origin issue (workers need
        // the URL of the script to be loaded, and dynamically loading a cross-origin
        // script does not work).
        //
        // PDFJS.disableWorker = true;

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
                scale = 0.8,
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

        })


    </script>
@stop
@section('content')

<input class="input" val="">
<button class="btn">restore</button>



<div id="summary"><p>
        I recently put together a PHP client library for FilePreviews and immediately thought about putting together a blog post on how I’d use it. After 6 years, according to this repo, of not writing a
        single line of PHP, I looked into Laravel since it seems to be the rave these days. Alright, let’s get to it.
    </p>
    <p>
        This is a step by step guide on how to use Laravel to upload files to S3, and generate previews and extract metadata using FilePreviews.io. If you are already uploading files to S3 with Laravel,
        check out how to integrate with FilePreviews.
    </p>
    <p>
        Create a Laravel project
    </p>
    <p>
        I’m assuming you’ll probably have composer already installed.
    </p>
    <p>
        $ composer create-project laravel/laravel --prefer-dist filepreviews-laravel-example
        Filesystem / Cloud Storage Setup
    </p>
    <p>
        In this example we’ll be using AWS S3 to store our files. After you’ve got a bucket and some credentials, let’s setup the project to use them.
    </p>
    <p>
        First we’ll add S3 support to Laravel
    </p>
    <p>
        $ composer require league/flysystem-aws-s3-v3
        Since no one want’s to commit their S3 credentials we’ll modify config/filesystems.php to use environment variables.
    </p>
</div>



<h1>'Previous/Next' example</h1>

<div>
    <button id="prev">Previous</button>
    <button id="next">Next</button>
    &nbsp; &nbsp;
    <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
</div>

<div style="width:491px; height:635px;">
{{--
    <canvas id="canvas" style="border:1px solid green; z-index: 1; width:491px; height:635px;position: absolute;left:0px; top:0;"></canvas>
    <canvas id="the-canvas" style="border:1px solid black; z-index: 0; position: absolute; top: 0px; left: 0px; "></canvas>
--}}
</div>

    <script>
        var canvas = document.getElementById('the-canvas'),
         canvas2 = document.getElementById('canvas'),
                ctx = canvas.getContext('2d'),
                ctx2 = canvas2.getContext('2d'),
                rect = {},
                drag = false;

        function init() {
            canvas2.addEventListener('mousedown', mouseDown, false);
            canvas2.addEventListener('mouseup', mouseUp, false);
            canvas2.addEventListener('mousemove', mouseMove, false);
        }
        function mouseDown(e) {
            rect.startX = e.pageX - this.offsetLeft;
            rect.startY = e.pageY - this.offsetTop;
            drag = true;

        }
        function mouseUp() {
            drag = false;
            draw(ctx);
        }
        function mouseMove(e) {
            if (drag) {
                rect.w = (e.pageX - this.offsetLeft) - rect.startX;
                rect.h = (e.pageY - this.offsetTop) - rect.startY ;
               ctx2.clearRect(0,0,canvas2.width,canvas2.height);
                draw(ctx2);
            }
        }
        function draw(d) {
            d.beginPath();
            d.lineWidth="1";
            d.strokeStyle="gray";
            d.rect(rect.startX, rect.startY, rect.w, rect.h);
            d.stroke();
        }
        init();

    </script>
@stop