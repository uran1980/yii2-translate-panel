var appSpoilers = appSpoilers || {};

// @see http://stackoverflow.com/questions/10896749/what-does-function-function-window-jquery-do
!(function ($) {
    // @see http://ejohn.org/blog/ecmascript-5-strict-mode-json-and-more/
    "use strict";

    /**
     * @see http://api.jquery.com/ready/
     */
    $(document).ready(function () {
        appSpoilers.spoiler = {
            init: function () {
                $(".spoiler-title.closed:not(.initialized)").closest('.spoiler').find('.spoiler-content:first').hide();
                $(".spoiler-title.opened:not(.initialized)").closest('.spoiler').find('.spoiler-content:first').show();

                $(".spoiler-title:not(.initialized)").each(function() {
                    $(this).addClass('initialized');
                    appSpoilers.spoiler.indication($(this));
                });
            },
            toggle: function (obj) {
                obj.closest('.spoiler').find('.spoiler-content:first').toggle();
            },
            indication: function (obj) {
                if (obj.closest('.spoiler').find('.spoiler-content:first').is(":hidden") ) {
                    obj.removeClass('opened').addClass('closed');
                    obj.find('.spoiler-indicator:first').addClass('icon-plus').removeClass('icon-minus');
                } else {
                    obj.removeClass('closed').addClass('opened');
                    obj.find('.spoiler-indicator:first').addClass('icon-minus').removeClass('icon-plus');
                }
            }
        };

        appSpoilers.actionsHandler = function () {
            $('body').delegate('.spoiler-title', 'click', function() {
                appSpoilers.spoiler.toggle($(this));
                appSpoilers.spoiler.indication($(this));
            });
        };

        /***********************************************************************
         *                               INIT
         **********************************************************************/
        appSpoilers.init = function () {
            appSpoilers.actionsHandler();
            appSpoilers.spoiler.init();
        };
        appSpoilers.init();
    });
})(window.jQuery);