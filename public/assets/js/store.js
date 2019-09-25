(function($) {
    'use strict';

    const storageKey = 'wishlist';
    let wishList = loadWishList();
    let $body = $('body');
    let $modal = $('#infoModal');

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

    $body.on('click', '.btn_ProceedDetails', function(event) {
        event.preventDefault();
        loadModal($(this));
        $modal.modal('show');
    });

    $modal.on('click', '#prevItem', function(event) {
        event.preventDefault();
        let button = $modal.data('prevModalButton');
        loadModal(button);
    });

    $modal.on('click', '#nextItem', function(event) {
        event.preventDefault();
        let button = $modal.data('nextModalButton');
        loadModal(button);
    });

    function loadModal(element) {
        let id = element.data('item-id');
        let title = element.data('item-title');
        let url = element.data('item-url');
        $modal.find('.modal-title').text(title);
        $modal.find('.modal-body').html('<div class="spinner-box"><div class="spinner-wrapper"><div class="spinner"></div></div></div>');

        //------ get prev and next dom-items from list -----
        let $items = $('.Item');
        let parentWrapper = element.parents('.Item').first();
        let currentIndex = parentWrapper.index('.Item');

        // get prev link
        if((currentIndex-1) > -1) {
            $modal.data('prevModalButton', $($items.get(currentIndex-1)).find('.btn_ProceedDetails'));
        } else {
            $modal.removeData('prevModalButton');
        }

        // get next link
        if((currentIndex+1) < $items.length) {
            $modal.data('nextModalButton', $($items.get(currentIndex+1)).find('.btn_ProceedDetails'));
        } else {
            $modal.removeData('nextModalButton');
        }
        //------

        $.get(url, function(html){
            $modal.find('.modal-body').html(html);
            highlightWishItem(id);
            activatePrevNext();
        });
    }

    function activatePrevNext() {
        if($modal.data('prevModalButton') === undefined) {
            $modal.find('.modal-body #prevItem').parents('li').addClass('inactive');
        } else {
            $modal.find('.modal-body #prevItem').parents('li').removeClass('inactive');
        }
        if($modal.data('nextModalButton') === undefined) {
            $modal.find('.modal-body #nextItem').parents('li').addClass('inactive');
        } else {
            $modal.find('.modal-body #nextItem').parents('li').removeClass('inactive');
        }
    }

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

    $body.on('click', '.btn_ProceedWishList', function(e) {
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
