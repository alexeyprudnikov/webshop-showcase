(function($) {
    'use strict';

    const storageKey = 'wishlist';
    let wishList = loadWishList();
    let $body = $('body');
    let $modal = $('#infoModal');

    let spinner = '<div class="spinner-wrapper"><div class="spinner"></div></div>';
    let spinnerFull = '<div class="spinner-box">'+spinner+'</div>';

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

    $modal.on('click', '.prevItem', function(event) {
        event.preventDefault();
        let button = $modal.data('prevModalButton');
        loadModal(button);
    });

    $modal.on('click', '.nextItem', function(event) {
        event.preventDefault();
        let button = $modal.data('nextModalButton');
        loadModal(button);
    });

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

    $body.on('click', '.btn_WishListRemove', function(e) {
        e.preventDefault();
        let $this = $(this);
        let itemId = $this.data('item-id');
        if($.inArray(itemId, wishList) > -1) {
            removeFromWishList(itemId);
            let parent = 'tr';
            if(wishList.length === 0) {
                parent = 'table';
            }
            $this.parents(parent).fadeOut(400, function () {
                if(wishList.length === 0) {
                    $('#wishList').html('Нет продуктов в избранном.<br>\n' +
                        '<a href="/">Вернуться к коллекции</a>.');
                } else {
                    $(this).remove();
                }
            });
        }
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

    /** functions **/

    function loadModal(element) {
        let id = element.data('item-id');
        let title = element.data('item-title');
        let url = element.attr('href');

        if(url === undefined) {
            return;
        }

        $modal.find('.modal-title').text(title);
        $modal.find('.modal-body').html(spinnerFull);

        //------ get prev and next dom-items from main items list, also with category and orderby -----
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
        let $prevItem = $modal.find('.modal-body .prevItem');
        if($modal.data('prevModalButton') === undefined) {
            $prevItem.parents('li').addClass('inactive');
            $prevItem.addClass('btn disabled');
        } else {
            $prevItem.attr('href', $modal.data('prevModalButton').attr('href'));
        }

        let $nextItem = $modal.find('.modal-body .nextItem');
        if($modal.data('nextModalButton') === undefined) {
            $nextItem.parents('li').addClass('inactive');
            $nextItem.addClass('btn disabled');
        } else {
            $nextItem.attr('href', $modal.data('nextModalButton').attr('href'));
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

    function loadWishList() {
        let wishList = localStorage.getItem(storageKey);
        if (!wishList) {
            localStorage.setItem(storageKey, JSON.stringify([]));
        } else {
            wishList = JSON.parse(wishList);
        }
        return wishList;
    }

    function parseWishList() {
        let $container = $('#wishList');
        $container.html(spinner);
        $.get('/wishlist/items', {ids: wishList.join()}, function(html){
            $container.html(html);
        });
    }

    if($('#wishList').length > 0) {
        parseWishList();
    }

    if($('.btn_ProceedWishList').length > 0) {
        highlightWishItems();
    }

    if($('#wishListCount').length > 0) {
        updateWishListCounter();
    }

})(jQuery);
