$('form.role-form').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var url = form.prop('action');
    form.find('.btn').attr('disabled', true);
    var data = form.serialize();
    form.find('.error').remove();

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'JSON'
    }).error(function (err) {
        var errors = JSON.parse(err.responseText);
        $.each(errors, function (k, v) {
            form.find('.' + k).after('<span class="error">' + v[0] + '</span>');
        });
    }).success(function (res) {
        if (res.result == 'success') {
            if(form.data('role') === 'user-form')
            {
                $('.role').append("<option value=" + res.name + ">" + res.display_name + "</option>").val(res.name).trigger("change");
                $('#role-form').modal('hide');
                $.toaster({message : res.message, priority : 'success'});
            } else {
                window.location.reload();
            }
        } else {
            form.find('.modal-header').after('<span class="error">' + res.message + '</span>');
        }
    }).complete(function () {
        form.find('.btn').removeAttr('disabled');
    })
});
