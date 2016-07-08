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

    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    $('#tabAll').click(function () {
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').each(function (i, t) {
            $(this).addClass('active');
        });
    });
    $('#tabMetadata').click(function () {
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').removeClass('active');
        $('.tab-pane-metadata').each(function (i, t) {
            $(this).addClass('active');
        });
    });
    $('#tabText').click(function () {
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').removeClass('active');
        $('.tab-pane-text').each(function (i, t) {
            $(this).addClass('active');
        });
    });

    $('#tabAnnotation').click(function () {
        $('#myTabs li').removeClass('active');
        $(this).parent("li").addClass('active');
        $('.comment-section').removeClass('active');
        $('.tab-pane-annotation').each(function (i, t) {
            $(this).addClass('active');
        });
    });

    $('#category-olc').click(function () {
        $('.landmatrix-page-wrap').show(150);
    });
    $('#category-rc').click(function () {
        $('.landmatrix-page-wrap').hide(150);
    });

    if ($('#category-olc').is(':checked')) {
        $('.landmatrix-page-wrap').show();
    }

});

$(function () {
    $(document).on('click', 'a.contract-discussion', function (e) {
        e.preventDefault();
        var discussionWrapper = $(this).parent().find('div.discussion-wrapper')
        if (discussionWrapper.length > 0) {
            discussionWrapper.slideToggle();
            return;
        }
        var $this = $(this);
        if ($this.data('loading') == false) {
            $this.data('loading', true);
            $.ajax({
                url: $(this).data('url')
            }).success(function (data) {
                $this.parent().append(data);
            }).complete(function () {
                $this.data('loading', 'false');
            });
        }
    });

    $(document).on('click', 'div.discussion-wrapper .btn-close', function(){
        $(this).parent().parent('div.discussion-wrapper').slideUp();
    });

    $(document).on('click', '.btn-comment-submit', function (e) {
        e.preventDefault();
        var parent = $(this).parent().parent();
        parent.find('div.error').remove();

        if (parent.find('.commentField').val() == '') {
            parent.find('.commentField').after('<div class="error">'+ lang_comment +'</div>');
            return false;
        }

        var action = $(this).data('url');
        var array = action.split('/');
        var key = '.key-' + array[array.length - 1];

        parent.find('.btn-primary').attr('disabled', 'disabled')
        $.ajax({
            url: action,
            type: 'Post',
            data: {status: parent.find('.status:checked').val() , comment: parent.find('.commentField').val()},
            dataType: "JSON",
            success: function (response) {
                if (response.result == true) {
                    var html = '';
                    $.each(response.message, function (index, dis) {
                        var status = dis.status == '1' ? ' <span class="label label-success pull-right">'+LANG.resolved+'</span>' : '';
                        html += '<div class="panel panel-default">' +
                            '<div class="panel-heading">' +
                            '<p class="comment-user"><i class="fa fa-user"></i> ' + dis.user.name + '</p>' +
                            status +
                            '<p class="comment-time"><i class="fa fa-clock-o"></i> ' + dis.created_at + '</p>' +
                            '</div>' +
                            '<div class="panel-body">' + nl2br(dis.message) + '</div>' +
                            '</div>';
                    });
                    parent.find('.comment-list').html(html).animate({
                        scrollTop: 0
                    });

                    parent.find('.commentField').val('');
                    var key_html = '';
                    if (response.message[0].status == 1) {
                        key_html = '<span class="label label-success">(' + response.message.length + ') '+ LANG.resolved+'</span>';
                    } else {
                        key_html = '<span  class="label label-red">(' + response.message.length + ')'+ LANG.open +'</span>';
                    }
                    $(key).html(key_html);


                } else {
                    parent.find('.commentField').after('<div class="error">' + response.message + '</div>')
                }
            },
            error: function (e) {
                alert('Connection error');
            },
            complete: function () {
                parent.find('.btn-primary').removeAttr('disabled');
            }
        });


        function nl2br(str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        }

    });

    if($('[data-toggle="tooltip"]').length > 0)
    {
        $('[data-toggle="tooltip"]').tooltip();
    }

    $('.translate').on('click',function(){
        var lang = $(this).data('lang');
        var url = window.location.href;

    });
});

