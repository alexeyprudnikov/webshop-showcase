(function($) {

    const mainAdminUrl = '/admin';

    $('.btn_Update').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        proceedUpdate(id);
    });
    $('.btn_Delete').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('data-id');
        proceedDelete(id);
    });
    $('.btn_Create').on('click', function(e) {
        e.preventDefault();
        proceedCreate();
    });
    $('.btn_Cancel').on('click', function(e) {
        e.preventDefault();
        window.location.href = mainAdminUrl;
    });

    let proceedUpdate = (id) => {
        let $form = $('form[name="itemForm_'+id+'"]');
        let formData = $form.serializeArray();
        formData.push({name: "id", value: id});

        $('.itemContainer', $form).html('updating...');

        $.ajax(
            {
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                success: function (Data) {
                    $('.itemContainer', $form).html(Data);
                }
            });
    };

    let proceedDelete = (id) => {

    };

    let proceedCreate = () => {
        let $form = $('form[name="createForm"]');
        let file = $('input[name="file"]', $form)[0].files[0];

        let formData = new FormData();
        $.each($form.serializeArray(), function(index, elem) {
            formData.append(elem.name, elem.value);
        });
        formData.append('file', file);

        $('.itemContainer', $form).html('creating...');

        $.ajax(
            {
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                success: function (Data) {
                    window.location.href = mainAdminUrl;
                }
            });

    };

})(jQuery);