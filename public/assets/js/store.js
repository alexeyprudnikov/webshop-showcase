
(function($) {
    'use strict';

    // Main Navigation
    $( '.hamburger-menu' ).on( 'click', function() {
        $(this).toggleClass('close');
        $('.site-branding').toggleClass('hide');
        $('.site-navigation').toggleClass('show');
        $('.site-header').toggleClass('no-shadow');
    });

    // Scroll to Next Section
    $( '.scroll-down' ).click(function() {
        $( 'html, body' ).animate({
            scrollTop: $( '.scroll-down' ).offset().top + 100
        }, 800 );
    });

    $('#infoModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let id = button.data('item-id');
        let title = button.data('item-title');
        let url = button.data('item-url');
        let modal = $(this);
        modal.find('.modal-title').text(title);
        modal.find('.modal-body').html('<div class="spinner-box"><div class="spinner-wrapper"><div class="spinner"></div></div></div>');
        $.get(url, function(html){
            modal.find('.modal-body').html(html);
        });
    })
})(jQuery);
