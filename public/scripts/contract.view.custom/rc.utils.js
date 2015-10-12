function htmlEncode(html) {
    return document.createElement('a').appendChild(document.createTextNode(html)).parentNode.innerHTML;
}

function htmlDecode(html) {
    var a = document.createElement('a'); 
    a.innerHTML = html;
    return a.textContent;
}