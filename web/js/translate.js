var appTranslate = appTranslate || {};

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
         * @param element (object)
         */
        appTranslate.save = function ($element) {
            var $row        = $element.closest('tr'),
                $td         = $row.find('.translation-tabs'),
                $form       = $td.find('form.translation-save-form'),
                url         = $element.attr('href'),
                $icon       = $element.find('i'),
                iconClass   = $icon.attr('class'),
                timeout     = 1200,
                delay       = 2500
            ;
            $form.ajaxSubmit({
                delegation: true,
                url: url,
                type: 'POST',
                dataType: 'json',
                beforeSubmit: function (data, form, options) {
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
                success: function (response, status, xhr, form) {
                    window.setTimeout(function () {
                        $.iGrowl.prototype.dismissAll('all');
                        window.setTimeout(function () {
                            $.iGrowl({
                                placement:  {
                                    x: $element.attr('placement-x') || 'center',
                                    y: $element.attr('placement-y') || 'top'
                                },
                                type:       response.status || 'error',
                                delay:      (response.status !== 'success') ? delay * 60 : delay,
                                animation:  true,
                                animShow:   'fadeIn',
                                animHide:   'fadeOut',
                                title:      ':: ' + ($element.attr('success-title') || 'SERVER RESPONSE') + ' .:',
                                message:    response.message || $element.attr('success-message') || '...'
                            });
                            if ( iconClass ) {
                                $icon.attr('class', iconClass);
                            }
                            window.setTimeout(function () {$element.data('locked', false);}, delay * 2);

                            if ( response.status === 'success' ) {
                                $row.addClass('success');
                                $form.addClass('has-success');
                            } else {
                                $row.addClass('danger');
                                $form.addClass('has-error');
                            }
                        }, timeout);
                    }, timeout);
                },
                error: function (response) {
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
                                message:    response.responseText || 'Error'
                            });
                            if ( iconClass ) {
                                $icon.attr('class', iconClass);
                            }
                            window.setTimeout(function () {$element.data('locked', false);}, delay * 2);

                            $row.addClass('danger');
                            $form.addClass('has-error');
                        }, timeout);
                    }, timeout);
                }
            });
        };

        /**
         * @param data (object)
         */
        appTranslate.delete = function (data) {
            var $element   = data.$element,
                $rowsCount = $element.closest('tbody').find('tr').length,
                $row       = $element.closest('tr')
            ;
            if ( data.status === 'success' ) {
                if ( $rowsCount > 1 ) {
                    $row.addClass('danger').fadeOut('slow', function () {
                        $row.remove();
                    });
                } else {
                    location.reload();
                }
            }
        };

        /**
         * @param data (object)
         */
        appTranslate.restore = function (data) {
            var $element   = data.$element,
                $rowsCount = $element.closest('tbody').find('tr').length,
                $row       = $element.closest('tr')
            ;
            if ( data.status === 'success' ) {
                if ( $rowsCount > 1 ) {
                    $row.addClass('success').fadeOut('slow', function () {
                        $row.remove();
                    });
                } else {
                    location.reload();
                }
            }
        };

        /**
         * set lang direaction
         *
         * @var $elements (obj)
         */
        appTranslate.setElementLangDirection = function ( $elements ) {
            var $elements = $elements || $('.tab-pane.active textarea');

            $($elements).each(function () {
                var lang = $(this).attr('rel') || 'en';

                if ( $.isEmptyObject(this) )
                    return;

                // set lang dir
                if ( 'ar' === lang ) {
                    $(this).attr('dir', 'rtl');
                } else {
                    $(this).attr('dir', 'ltr');
                }
            });
        };

        /**
         * @param $element (object)
         */
        appTranslate.copy = function ($element) {
            var $row            = $element.closest('tr'),
                $textarea       = $row.find('.tab-pane.active textarea'),
                sourceMessage   = $row.find('.source-message .source-message-content').text()
            ;
            if ( sourceMessage ) {
                $textarea.val(sourceMessage.replace(/@@/g, '')).focus();
            }
        };

        /***********************************************************************
         *                          ACTIONS HANDLER
         **********************************************************************/
        appTranslate.actionsHandler = function () {
            $('.btn-translation-save').on('click', function () {
               appTranslate.save($(this));
               return false;
            });
            $(document).on('ajaxButtonSubmit', function (event, data) {
                if ( data.action === 'translation-delete' ) {
                    appTranslate.delete(data);
                }
                if ( data.action === 'translation-restore' ) {
                    appTranslate.restore(data);
                }
            });
            $('.btn-translation-copy-from-source').on('click', function () {
                appTranslate.copy($(this));
                return false;
            });
        };

        /***********************************************************************
         *                               INIT
         **********************************************************************/
        appTranslate.init = function () {
            appTranslate.actionsHandler();
            appTranslate.setElementLangDirection();
        };
        appTranslate.init();
    });
})(window.jQuery);