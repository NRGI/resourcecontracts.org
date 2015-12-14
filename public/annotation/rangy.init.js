var highlighter;

    rangy.init();
    highlighter = rangy.createHighlighter();

    function getRandomIntInclusive(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    highlighter.addClassApplier(rangy.createClassApplier("note", {
        ignoreWhiteSpace: true,
        elementTagName: "span",
        elementProperties: {
            onclick: function () {
                var highlight = highlighter.getHighlightForElement(this);
                if (window.confirm("Delete this note (ID " + highlight.id + ")?")) {
                    highlighter.removeHighlights([highlight]);
                }
                return false;
            }
        }
    }));

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

    function deserializeSelection() {
        restoreSelection(document.getElementById('summary'), JSON.parse($('.input').val()));
        highlighter.highlightSelection("note", {containerElementId: "summary"});

        restoreSelection(document.getElementById('summary'), JSON.parse('{"start":374,"end":382}'));
        highlighter.highlightSelection("note", {containerElementId: "summary"});

        restoreSelection(document.getElementById('summary'), JSON.parse('{"start":722,"end":772}'));
        highlighter.highlightSelection("note", {containerElementId: "summary"});

        restoreSelection(document.getElementById('summary'), JSON.parse('{"start":722,"end":772}'));
        highlighter.highlightSelection("note", {containerElementId: "summary"});


        window.getSelection().removeAllRanges();
    }


$(function () {

    $(document).on('click', '.remove-hl', function (e) {
        var id = $(this).data('key');
        var el = document.getElementById(id);

        $('#' + id).removeAttr('id');

        var highlight = highlighter.getHighlightForElement(el);
        if (window.confirm("Delete this note (ID " + highlight.id + ")?")) {
            highlighter.removeHighlights([highlight]);
        }
        $(this).parent().remove();
        e.preventDefault();
    });


    $(document).on('mouseup', 'div#summary', function () {
        var sel = rangy.getSelection();
        var text = sel.toString().trim();

        if (text != '') {
            var confirmthis = confirm('Do you want to annotate this selected text?');
            if (confirmthis) {
                var save = saveSelection(document.getElementById('summary'));
                var hl = highlighter.highlightSelection("note", {containerElementId: "summary"});
                var key = getRandomIntInclusive(1000, 9999);
                if (sel.rangeCount > 0) {
                    var range = sel.getRangeAt(0);
                    console.log(range);
                    var parentElement = range.commonAncestorContainer;
                    alert(parentElement.nodeType);
                    if (parentElement.nodeType == 3) {
                        parentElement = parentElement.parentNode;
                    }
                    else {
                        rangy.getSelection().removeAllRanges();
                       // alert('Selection will be merged!');
                        //return false;
                    }
                }
                $(parentElement).attr('id', key);
                var page = $(parentElement).parent('.page').data('page');

                var code = JSON.stringify(save);
                var id = hl[0].id;
                var html = '<li>' + text + '<p> Page: #' + page + '<br/></p>' +
                    '<input type="hidden" name="annotation[' + key_interval + '][type]" value="text"/>' +
                    '<input type="hidden" name="annotation['+key_interval+'][text]" value="' + text + '"/>' +
                    "<input type='hidden' name='annotation["+key_interval+"][position]' value='" + code + "'/>" +
                    '<input type="hidden" name="annotation['+key_interval+'][page]" value="' + page + '"/>' +
                    '<a href="#" class="remove-hl btn btn-danger" data-key="' + key + '" data-hlid="' + id + '">-</a>' +
                    '</li>';
                $('.annotated-text-list').append(html);
                key_interval++

                rangy.getSelection().removeAllRanges();
            }
            else {
                rangy.getSelection().removeAllRanges();
            }
        }
    });
});
