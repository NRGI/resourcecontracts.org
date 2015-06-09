$(document).ready(function () {
    $('.dropdown').click(function () {
        $(this).children('ul').slideToggle();
        $(this).children('a').toggleClass('expanded');
    });

    $(".sidebar").mCustomScrollbar();

    $('.search-link').click(function () {
        $(this).css('display', 'none');
        $('.close').css('display', 'block');
        $(this).siblings('.search-input-wrapper').slideToggle();
        $(this).parents('.top-container').toggleClass('expand');
    });

    $('.close').click(function () {
        $(this).css('display', 'none');
        $('.open').css('display', 'block');
    });

    $('.confirm').on('click', function (e) {
        if (confirm($(this).data('confirm'))) {
            return true;
        }
        else {
            return false;
        }
    })

    $('.user-wrapper').click(function(){
        $(this).children('ul').toggle();
        $(this).children('a').toggleClass('expanded');
    });

});