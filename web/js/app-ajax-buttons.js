var appAjaxButtons = appAjaxButtons || {};

// @see http://stackoverflow.com/questions/10896749/what-does-function-function-window-jquery-do
!(function ($) {
    // @see http://ejohn.org/blog/ecmascript-5-strict-mode-json-and-more/
    "use strict";

    /**
     * @see http://api.jquery.com/ready/
     */
    $(document).ready(function () {
        /***********************************************************************
         *                              METHODS
         **********************************************************************/
        /**
         * @param $element (obj)
         */
        appAjaxButtons.ajaxButtonSubmit = function ( $element ) {
            var url         = $element.attr('href'),
                postData    = $element.attr('data') || '',
                $icon       = $element.find('i'),
                iconClass   = $icon.attr('class'),
                timeout     = 1200,
                delay       = 2500
            ;

            // confirm btn clicked
            if ( $element.attr('data-apply') === 'confirmation' ) {
                $element    = $element.closest('span.btn-ajax-wrap').find('.btn-ajax').attr('href', url);
                postData    = $element.attr('data') || '';
                $icon       = $element.find('i');
                iconClass   = $icon.attr('class');

                $element.removeAttr('data-toggle');
            }

            if ( !url || $element.data('locked') === true || $element.attr('data-toggle') === 'confirmation' ) {
                return false;
            }

            $.ajax({
                type: 'POST',
                data: postData,
                url: url,
                beforeSend: function ( xhr, settings ) {
                    $element.data('locked', true);
                    if ( iconClass ) {
                        $icon.attr('class', 'fa fa-spinner fa-pulse');
                    }
                    if ( $element.attr('before-send-message') ) {
                        // show iGrowl popup message
                        // @see http://catc.github.io/iGrowl/
                        $.iGrowl.prototype.dismissAll('all');
                        $.iGrowl({
                            placement:  {
                                x: $element.attr('placement-x') || 'center',
                                y: $element.attr('placement-y') || 'top'
                            },
                            type:       'notice',
                            delay:      delay * 60,
                            animation:  true,
                            animShow:   'fadeIn',
                            animHide:   'fadeOut',
                            title:      ':: ' + ($element.attr('before-send-title') || 'REQUEST SENT') + ' .:',
                            message:    $element.attr('before-send-message') || 'Please wait...'
                        });
                    }
                },
                success: function ( data ) {
                    window.setTimeout(function () {
                        $.iGrowl.prototype.dismissAll('all');
                        window.setTimeout(function () {
                            $.iGrowl({
                                placement:  {
                                    x: $element.attr('placement-x') || 'center',
                                    y: $element.attr('placement-y') || 'top'
                                },
                                type:       data.status || 'success',
                                delay:      (data.status !== 'success') ? delay * 60 : delay,
                                animation:  true,
                                animShow:   'fadeIn',
                                animHide:   'fadeOut',
                                title:      ':: ' + ($element.attr('success-title') || 'SERVER RESPONSE') + ' .:',
                                message:    data.message || $element.attr('success-message') || '...'
                            });
                            if ( iconClass ) {
                                $icon.attr('class', iconClass);
                            }
                            window.setTimeout(function () {$element.data('locked', false);}, delay * 2);
                            // triger event: "ajaxButtonSubmit" ----------------
                            $(document).trigger('ajaxButtonSubmit', {
                                $element: $element,
                                status: data.status,
                                url: url,
                                action: $element.attr('action'),
                                data: data
                            });
                            // -------------------------------------------------
                        }, timeout);
                    }, timeout);
                },
            }).then(function () {                                               // doneCallbacks (@see http://api.jquery.com/deferred.then/)
                // dummy
            }, function ( xhr, errorType, exception ) {                         // failCallacks
                window.setTimeout(function () {
                    $.iGrowl.prototype.dismissAll('all');
                    window.setTimeout(function () {
                        $.iGrowl({
                            placement:  {
                                x: $element.attr('placement-x') || 'center',
                                y: $element.attr('placement-y') || 'top'
                            },
                            type:       'error',
                            delay:      delay * 60,
                            animation:  true,
                            animShow:   'fadeIn',
                            animHide:   'fadeOut',
                            title:      ':: SERVER ERROR .:',
                            message:    xhr.responseText || 'Error'
                        });
                        if ( iconClass ) {
                            $icon.attr('class', iconClass);
                        }
                        window.setTimeout(function () {$element.data('locked', false);}, delay * 2);
                    }, timeout);
                }, timeout);
            });
        };

        /***********************************************************************
         *                          ACTIONS HANDLER
         **********************************************************************/
        appAjaxButtons.actionsHandler = function () {
            try{$('[data-toggle="confirmation"]').confirmation();}catch(ex){}
            $('body').delegate('.btn-ajax, a[data-apply="confirmation"]', 'click', function () {
                appAjaxButtons.ajaxButtonSubmit($(this));
                return false;
            });
        };

        /***********************************************************************
         *                               INIT
         **********************************************************************/
        appAjaxButtons.init = function () {
            appAjaxButtons.actionsHandler();
        };
        appAjaxButtons.init();
    });
})(window.jQuery);