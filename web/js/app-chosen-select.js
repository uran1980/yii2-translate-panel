// @see http://harvesthq.github.com/chosen/

var appChosenSelect = appChosenSelect || {};

// @see http://stackoverflow.com/questions/10896749/what-does-function-function-window-jquery-do
!(function ($) {
    // @see http://ejohn.org/blog/ecmascript-5-strict-mode-json-and-more/
    "use strict";

    /**
     * @see http://api.jquery.com/ready/
     */
    $(document).ready(function () {
        appChosenSelect.initChosen = function () {
            $('.chosen-select').each(function () {
                var disableSearchThreshold  = $(this).attr('disable_search_threshold') || 10,
                    disableSearch           = $(this).attr('disable_search') || false,
                    parent                  = $(this).parent(),
                    width                   = parent.width()
                ;
                parent.css('width', width + 'px');
                // set chosen select params
                $(this).chosen({
                    disable_search_threshold: disableSearchThreshold,
                    search_contains: true,
                    disable_search: disableSearch
                });
            });
        };

        /***********************************************************************
         *                               INIT
         **********************************************************************/
        appChosenSelect.init = function () {
            appChosenSelect.initChosen();
        };
        appChosenSelect.init();
    });
})(window.jQuery);