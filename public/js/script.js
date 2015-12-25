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
    $("#show-new-document").click(function (e) {
        e.preventDefault();
        $("#new-document").toggle();
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
        $.ajax({
            url: $(this).prop('href')
        }).success(function (data) {
            $this.parent().append(data);
        });
    });


    $(document).on('click', 'div.discussion-wrapper .btn-close', function(){
            

    });

    $(document).on('submit', '#commentForm', function (e) {
        e.preventDefault();
        var $this = $(this);
        $this.find('div.error').remove();

        if ($this.find('#commentField').val() == '') {
            $('#commentField').after('<div class="error"> Comment is required.</div>');
            return false;
        }

        var action = $this.prop('action');
        var array = action.split('/');
        var key = '.key-' + array[array.length - 1];

        $this.find('.btn-primary').attr('disabled', 'disabled')
        $.ajax({
            url: action,
            type: $this.prop('method'),
            data: $this.serialize(),
            dataType: "JSON",
            success: function (response) {
                if (response.result == true) {
                    var html = '';
                    $.each(response.message, function (index, dis) {
                        var status = dis.status == '1' ? ' <span class="label label-success pull-right">Resolved</span>' : '';
                        html += '<div class="panel panel-default">' +
                            '<div class="panel-heading">' +
                            '<p class="comment-user"><i class="fa fa-user"></i> ' + dis.user.name + '</p>' +
                            status +
                            '<p class="comment-time"><i class="fa fa-clock-o"></i> ' + dis.created_at + '</p>' +
                            '</div>' +
                            '<div class="panel-body">' + nl2br(dis.message) + '</div>' +
                            '</div>';
                    });
                    $('.comment-list').html(html).animate({
                        scrollTop: 0
                    });

                    $this.find('#commentField').val('');
                    var key_html = '';
                    if (response.message[0].status == 1) {
                        key_html = '<span class="label label-success">(' + response.message.length + ') Resolved</span>';
                    } else {
                        key_html = '<span  class="label label-red">(' + response.message.length + ') Open</span>';
                    }
                    $(key).html(key_html);


                } else {
                    $this.find('#commentField').after('<div class="error">' + response.message + '</div>')
                }
            },
            error: function (e) {
                alert('Connection error');
            },
            complete: function () {
                $this.find('.btn-primary').removeAttr('disabled');
            }
        });

        function nl2br(str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        }

    });
})

