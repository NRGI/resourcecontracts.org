$(document).ready(function () {
    $('.confirm').on('click', function (e) {
        if (confirm($(this).data('confirm'))) {
            return true;
        }
        else {
            return false;
        }
    });

    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

});