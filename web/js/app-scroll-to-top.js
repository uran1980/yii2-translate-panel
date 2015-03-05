var appScrollToTop = appScrollToTop || {};

// @see http://stackoverflow.com/questions/10896749/what-does-function-function-window-jquery-do
!(function ($) {
    // @see http://ejohn.org/blog/ecmascript-5-strict-mode-json-and-more/
    "use strict";

    /**
     * @see http://api.jquery.com/ready/
     */
    $(document).ready(function () {
        appScrollToTop.actionsHandler = function () {
            $(window).scroll(function() {
                if ( $(this).scrollTop() > 500 ) {
                    $('.scroll-to-top-link').attr('href', '').fadeIn();
                }
                else {
                    $('.scroll-to-top-link').fadeOut();
                }
            });
            $('.scroll-to-top-link').click(function () {
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                return false;
            });
        };

        /***********************************************************************
         *                               INIT
         **********************************************************************/
        appScrollToTop.init = function () {
            appScrollToTop.actionsHandler();
        };
        appScrollToTop.init();
    });
})(window.jQuery);