$(function () {
    $('select').select2({placeholder: "Select", allowClear: true, theme: "classic"});
    $('.parent_company').select2({placeholder: "Select", allowClear: true, tags: true, theme: "classic"});
    $('.resource-list').select2({placeholder: "Select", allowClear: true, tags: true, theme: "classic"});
    $('.contract-form').validate();

    $('.datepicker').datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        scrollInput: false
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

    $(document).on('click', '.company .item .delete', function (e) {
        $(this).parent().remove();
        var key = $(this).data('key');
        if (typeof key != 'undefined') {
            var input = $('.delete_company');
            key = input.val() == '' ? key : input.val() + ',' + key;
            input.val(key);
        }
    });
    $('.new-company').on('click', function (e) {
        e.preventDefault();
        i += 1;
        var template = $('#company-template').html();
        Mustache.parse(template);
        var rendered = Mustache.render(template, {item: i});
        $('.company .item:last-child').after(rendered);
        $('select').select2({placeholder: "Select", allowClear: true, theme: "classic"});
        $('.parent_company').select2({placeholder: "Select", allowClear: true, tags: true, theme: "classic"});

        $('.datepicker').datetimepicker({
            timepicker: false,
            format: 'Y-m-d',
            scrollInput: false
        });
    })

    $(document).on('change', '#corporate_grouping', function () {
        var corporate_grouping_mode = '<input class="form-control corporate_grouping_other" name="company[' + i + '][parent_company]" type="text">';
        if (($(this).val() == 'Other')) {
            $(this).parent().append(corporate_grouping_mode)
        } else {
            if ($('.corporate_grouping_other').length) {
                corporate_grouping_mode = $('.corporate_grouping_other');
            }
            $('.corporate_grouping_other').remove();
        }
    });


    $(document).on('click', '.concession .con-item .delete', function (e) {
        $(this).parent().remove();
        var key = $(this).data('key');
        if (typeof key != 'undefined') {
            var input = $('.delete_concession');
            key = input.val() == '' ? key : input.val() + ',' + key;
            input.val(key);
        }
    });

    $('.new-concession').on('click', function (e) {
        e.preventDefault();
        j += 1;
        var template = $('#concession-template').html();
        Mustache.parse(template);
        var rendered = Mustache.render(template, {item: j});
        $('.concession .con-item:last-child').after(rendered);
    })

    $(document).on('click', '.government_entity .government-item .delete', function (e) {
        $(this).parent().remove();
        var key = $(this).data('key');
        if (typeof key != 'undefined') {
            var input = $('.delete_government_entity');
            key = input.val() == '' ? key : input.val() + ',' + key;
            input.val(key);
        }
    });

    $('.new-government-entity').on('click', function (e) {
        e.preventDefault();
        g += 1;
        var template = $('#government-entity').html();
        Mustache.parse(template);
        var rendered = Mustache.render(template, {item: g});
        $('.government_entity .government-item:last-child').after(rendered);
        console.log(g);
    })


    $(document).on('click', '.selected-document .document .delete', function (e) {
        $(this).parent().remove();
        var id = $(this).context.id;
        docId.pop(Number(id));
    });
    var eventSelect = $(".select-document");
    eventSelect.on("select2:select", function (e) {
        var args = JSON.stringify(e.params, function (key, value) {
            var data = value.data;


            var check = docId.indexOf(Number(data.id));
            docId.push(Number(data.id));
            var template = $('#document').html();
            Mustache.parse(template);
            var rendered = Mustache.render(template, {id: data.id, name: data.text});
            if (check < 0) {
                $("#selected-document").append(rendered);
            }
        });

    });

});



