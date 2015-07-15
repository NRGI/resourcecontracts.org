$(document).ready(function () {
    $('.confirm').on('click', function (e) {
        if (confirm($(this).data('confirm'))) {
            return true;
        }
        else {
            return false;
        }
    });

    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')}
    });

    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    $('#tabAll').click(function(){
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').each(function(i,t){
            $(this).addClass('active');
        });
    });
    $('#tabMetadata').click(function(){
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').removeClass('active');
        $('.tab-pane-metadata').each(function(i,t){
            $(this).addClass('active');
        });
    });
    $('#tabText').click(function(){
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').removeClass('active');
        $('.tab-pane-text').each(function(i,t){
            $(this).addClass('active');
        });
    });

    $('#tabAnnotation').click(function(){
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').removeClass('active');
        $('.tab-pane-annotation').each(function(i,t){
            $(this).addClass('active');
        });
    });

});