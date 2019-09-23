(function($) {
    'use strict';

    const mainAdminUrl = '/admin';
    const signOutUrl = '/admin/logout';

    $('.btn_Create').on('click', function(e) {
        e.preventDefault();
        proceedCreate();
    });

    $('.btn_SignOut').on('click', function(e) {
        e.preventDefault();
        window.location.href = signOutUrl;
    });

    let $tbl = $('#tbl_List');

    $tbl.on('click', '.btn_Update', function(e) {
        e.preventDefault();
        let route = $(this).attr('data-route');
        let id = $(this).attr('data-id');
        proceedUpdate(route, id);
    });
    $tbl.on('click', '.btn_Delete', function(e) {
        e.preventDefault();
        let route = $(this).attr('data-route');
        let id = $(this).attr('data-id');
        proceedDelete(route, id);
    });

    // search filter
    $('#Search').on('keyup', function(e) {
        let item = $('tr.itemContainer', '#tbl_List');
        let filter = $(this).val();
        if(filter === '') {
            item.show();
            return;
        }
        let regex = new RegExp(filter, 'i');
        item.each(function() {
            if($(this).attr('data-search').search(regex) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });

    let proceedCreate = () => {
        let $container = $('#showCreateModal');
        let $form = $('form[name="createForm"]');
        let file = $('input[name="file"]', $form)[0].files[0];

        let formData = new FormData();
        $.each($form.serializeArray(), function(index, elem) {
            formData.append(elem.name, elem.value);
        });
        formData.append('file', file);

        $('input, select, button', $container).attr('disabled', 'disabled');

        $('#infoText').html('creating...');

        $.ajax(
            {
                type: 'POST',
                url: $form.attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                success: function (Data) {
                    window.location.href = mainAdminUrl;
                },
                error: function(xhr, textStatus, errorThrown) {
                    $('#infoText').html('Error creating: ' + xhr.responseText);
                    $('input, select, button', $container).removeAttr('disabled');
                }
            });

    };

    let proceedUpdate = (route, id) => {
        let $container = $('#itemContainer_'+id);
        let file = $('input[name="file"]', $container)[0].files[0];

        let formData = new FormData();
        $('input, select', $container).each(function(index) {
            if(!$(this).is('input[type="file"]')) {
                if($(this).is('input[type="checkbox"]')) {
                    formData.append($(this).attr('name'), this.checked ? '1' : '0');
                } else {
                    formData.append($(this).attr('name'), $(this).val());
                }
            }
        });
        formData.append('file', file);
        formData.append('id', id);

        $('input, select, button', $container).attr('disabled', 'disabled');

        $('#infoText').html('updating...');

        $.ajax(
            {
                type: 'POST',
                url: route,
                data: formData,
                contentType: false,
                processData: false,
                success: function (Data) {
                    $('#infoText').html('');
                    $container.html(Data);
                },
                error: function(xhr, textStatus, errorThrown) {
                    $('#infoText').html('Error updating: ' + xhr.responseText);
                    $('input, select, button', $container).removeAttr('disabled');
                }
            });
    };

    let proceedDelete = (route, id) => {

        let c = confirm("Delete item. Are you sure?");

        if (c === true) {
            let $container = $('#itemContainer_'+id);

            $('#infoText').html('deleting...');

            $container.fadeOut();

            $.ajax(
                {
                    type: 'POST',
                    url: route,
                    data: {id: id},
                    success: function () {
                        $('#infoText').html('');
                        $container.remove()
                    }
                });
        }
    };

})(jQuery);