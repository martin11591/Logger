function $(sel) {
    tmp = document.getElementById(sel);
    return tmp ? tmp : document.getElementsByClassName(sel);
}

function scaleFont(target, root) {
    if (isNaN(root) || root === undefined) root = rootFontSize;
    return parseFloat(target) / parseFloat(root) + "em";
}

function rescale(zoom) {
    if (isNaN(zoom) || zoom === undefined) zoom = pageZoom;
    pageOutside.style.fontSize = (pageFontSize * (zoom / 100)) + "px";
}

var pageOutside = $('pageOutside')[0];
var page = $('page')[0];

var rootFontSize = parseFloat(window.getComputedStyle(document.body).fontSize);
var pageFontSize = parseFloat(window.getComputedStyle(pageOutside).fontSize);
var pageZoom = 100;
var pageZoomStep = 0.1;

setInterval(function() {
    pageZoom += pageZoomStep;
    if (pageZoom <= 10 || pageZoom >= 400) pageZoomStep = -pageZoomStep;
    rescale(pageZoom);
},1);