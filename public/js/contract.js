$(function () {
    $('select').select2({placeholder: "Select", allowClear: true, theme: "classic"});
    $('.contract-form').validate();

    $('.datepicker').datetimepicker({
        timepicker: false,
        format: 'Y-m-d'
    });

    translation();

    function translation() {
        var div = $('.translation-parent');
        if ($('.translation:checked').val() == 1) {
            div.removeClass('hide');
        }
        else {
            div.addClass('hide');
        }
    }

    $('.translation').on('change', function () {
        translation();
    });
});
