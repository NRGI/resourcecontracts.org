@section('css')
    <link href="{{asset('css/bootstrap-editable.css')}}" rel="stylesheet"/>
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <style>
        textarea.form-control.input-large {
            width: 500px !important;
        }

        span.pull-left {
            margin-right: 5px;
        }

        .input-sm {
            width: 400px !important;
        }

        .edit-annotation-text {
            display: block;
        }

        .editable-click, a.editable-click, a.editable-click:hover {
            text-decoration: none;
            border-bottom: none !important;
        }

        .editable-container.editable-inline {
            vertical-align: top;
        }
    </style>
@stop

<?php
$pages = [];
for ($i = 1; $i <= $contract->pages()->count(); $i ++) {
    $pages[$i] = $i;
}
?>

@section('script')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/bootstrap-editable.min.js')}}"></script>
    <script>
        $(function () {
            $.fn.editable.defaults.mode = 'inline';
            $('.edit-annotation-text, .edit-annotation-section').on('click', function () {
                        $(this).editable();
                    }
            );

            $('.annotation-delete-btn').on('click', function (e) {
                        e.preventDefault();
                        if (!confirm("{{_l("annotation.delete_confirm")}}")) {
                            return;
                        }
                        var parent = $(this).parent().parent();
                        parent.fadeOut('slow');
                        var id = $(this).data('pk');
                        var url = app_url + "/api/annotation/" + id + "/delete";
                        $.ajax({
                            url: url,
                            type: "POST",
                            data: {'id': id},
                            success: function (data) {
                                if(parent.parent().find('.row').length == 1)
                                {
                                    parent = parent.parent();
                                    $('.annotation-count').html(function(index, count){
                                        return parseInt(count)-1;
                                    });
                                }
                                parent.remove();

                            },
                            error: function () {
                                parent.fadeIn('slow');
                            }
                        });
                    }
            );

            $('.edit-annotation-category').on('click', function () {
                        var categories = {!!json_encode(trans("codelist/annotation.annotation_category"))!!};
                        $(this).editable({
                            source: categories,
                            select2: {
                                width: 400,
                                placeholder: 'Select category',
                                allowClear: true
                            }
                        });
                    }
            );
            $('.edit-annotation-page').on('click', function () {
                        var pages = {!!json_encode($pages)!!};
                        $(this).editable({
                            source: pages,
                            select2: {
                                width: 400,
                                placeholder: 'Select pages',
                                allowClear: true
                            }
                        });
                    }
            );
            var form = $('.output-type-form');

            $(form).on('submit', function (e) {
                e.preventDefault();
                var type = form.find('input[type=radio]:checked').val();
                if (typeof type != 'undefined') {
                    $.ajax({
                        url: form.attr('action'),
                        data: form.serialize(),
                        type: 'POST',
                        dataType: 'json'
                    }).done(function (response) {
                        window.location.reload()
                    })
                }
                else {
                    alert('Please select text type');
                }
            });

            var suggestion_form = $('.suggestion-form');
            $(suggestion_form).on('submit', function (e) {
                var text = $(this).find('#message').val();
                var status = $(this).find('#status').val();
                if (text == '' && status == 'rejected') {
                    e.preventDefault();
                    alert('Suggestion message is required.');
                    return false;
                }
                else {
                    $(this).find('input[type=submit]').text('loading...');
                    $(this).find('input[type=submit]').attr('disabled', 'disabled');
                    return true;
                }
            });
        })
    </script>
@stop