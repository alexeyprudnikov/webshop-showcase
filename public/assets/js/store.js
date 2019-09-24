(function($) {
    'use strict';

    const storageKey = 'wishlist';
    let wishList = loadWishList();

    // Main Navigation
    $('.hamburger-menu').on('click', function() {
        $(this).toggleClass('close');
        $('.site-branding').toggleClass('hide');
        $('.site-navigation').toggleClass('show');
        $('.site-header').toggleClass('no-shadow');
    });

    // Scroll to Next Section
    $('.scroll-down').click(function() {
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
            highlightWishItem(id);
        });
    });

    function highlightWishItems() {
        for (let i = 0; i < wishList.length; i++) {
            $('.btn_ProceedWishList[data-item-id='+wishList[i]+']').addClass('item-action-active');
        }
    }

    function highlightWishItem(itemId) {
        if($.inArray(itemId, wishList) !== -1) {
            $('.btn_ProceedWishList[data-item-id='+itemId+']').addClass('item-action-active');
        } else {
            $('.btn_ProceedWishList[data-item-id='+itemId+']').removeClass('item-action-active');
        }
    }

    function updateWishListCounter() {
        $('#wishListCount').text(wishList.length);
    }

    $('body').on('click', '.btn_ProceedWishList', function(e) {
        e.preventDefault();
        let $this = $(this);
        let itemId = $this.data('item-id');
        if($.inArray(itemId, wishList) === -1) {
            addToWishList(itemId);
        } else {
            removeFromWishList(itemId);
        }
        highlightWishItem(itemId);
        updateWishListCounter();
    });

    let addToWishList = (itemId) => {
        wishList.push(itemId);
        localStorage.setItem(storageKey, JSON.stringify(wishList));
    };

    let removeFromWishList = (itemId) => {
        for (let i = 0; i < wishList.length; i++) {
            if (itemId === wishList[i]) {
                wishList.splice(i,1);
            }
        }
        localStorage.setItem(storageKey, JSON.stringify(wishList));
    };

    function loadWishList() {
        let wishList = localStorage.getItem(storageKey);
        if (!wishList) {
            localStorage.setItem(storageKey, JSON.stringify([]));
        } else {
            wishList = JSON.parse(wishList);
        }
        return wishList;
    }

    highlightWishItems();
    updateWishListCounter();

})(jQuery);
