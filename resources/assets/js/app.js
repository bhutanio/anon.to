var BASEURL = $('meta[name=_base_url]').attr('content');

var shortenUrl = function () {
    $('input[name="short_url"]').on('click', function () {
        $(this).select();
    });

    $('#form_shortener').on('submit', function (e) {
        var form = $(this);

        form.find('.form-error').remove();
        form.find(':submit').addClass('disabled').attr('disabled', 'disabled');
        form.find('.shorten-output').append('<i class="save-spinner glyphicon glyphicon-refresh glyphicon-spin"></i>');

        $.ajax({
            url: form.attr("action"),
            type: 'POST',
            data: form.serializeArray(),
            dataType: 'json'
        }).done(function (data) {
            form.find('.input-group').removeClass('has-error');
            if (data && data.url) {
                form.find('.short-url-group').removeClass('hidden');
                form.find('input[name="short_url"]').val(data.url);
            }
        }).fail(function (jqXHR) {
            form.find('.short-url-group').addClass('hidden');
            form.find('.input-group').addClass('has-error');
            if ($.type(jqXHR.responseJSON) == 'string') {
                form.find('.shorten-output').append('<span class="help-block form-error text-danger">' + jqXHR.responseJSON + '</span>');
            } else if ($.type(jqXHR.responseJSON) == 'object') {
                $.each(jqXHR.responseJSON, function (index, value) {
                    if (value.length != 0) {
                        form.find('.shorten-output').append('<span class="help-block form-error text-danger">' + value + '</span>');
                    }
                });
            } else {
                form.find('.shorten-output').append('<span class="help-block form-error text-danger">' + jqXHR.statusText + '</span>');
            }
        }).always(function () {
            form.find('.save-spinner').remove();
            form.find(':submit').removeClass('disabled').removeAttr('disabled');
        });
        e.preventDefault();
    });
    $.ajax({
        url: BASEURL + '/csrf',
        type: 'GET'
    }).done(function (data) {
        $('input[name="_token"]').val(data);
    });
};