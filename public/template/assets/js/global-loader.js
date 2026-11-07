(function ($) {
    'use strict';

    var activeAjaxRequests = 0;
    var navigationPending = false;
    var $loader = null;
    var storageKey = 'si-show-loader';

    function showLoader() {
        if (!$loader || !$loader.length) {
            return;
        }
        $loader.stop(true, true).css('display', 'flex').hide().fadeIn(150);
    }

    function hideLoader(instant, force) {
        if (!$loader || !$loader.length) {
            return;
        }
        if (!force && (navigationPending || activeAjaxRequests > 0)) {
            return;
        }
        if (force) {
            navigationPending = false;
            activeAjaxRequests = 0;
        }
        if (instant) {
            $loader.stop(true, true).hide();
        } else {
            $loader.stop(true, true).fadeOut(150);
        }
    }

    function shouldIgnoreLink($link) {
        var href = ($link.attr('href') || '').trim();
        if (!href || href === '#' || href.indexOf('javascript:') === 0) {
            return true;
        }
        if ($link.attr('target') === '_blank' || $link.attr('download') !== undefined) {
            return true;
        }
        if ($link.attr('data-no-loader') !== undefined || $link.closest('[data-no-loader]').length) {
            return true;
        }
        if (href.charAt(0) === '#') {
            return true;
        }
        return false;
    }

    function isSameOriginNavigation(href) {
        try {
            return new URL(href, window.location.origin).origin === window.location.origin;
        } catch (error) {
            return href.charAt(0) === '/';
        }
    }

    function markNavigationLoader() {
        try {
            sessionStorage.setItem(storageKey, '1');
        } catch (error) {
            // Ignore storage errors in private mode.
        }
        navigationPending = true;
        showLoader();
    }

    function initPendingNavigationLoader() {
        try {
            if (sessionStorage.getItem(storageKey) === '1') {
                sessionStorage.removeItem(storageKey);
                navigationPending = true;
                showLoader();
            }
        } catch (error) {
            // Ignore storage errors in private mode.
        }
    }

    function init() {
        $loader = $('#global-loader');
        if (!$loader.length) {
            return;
        }

        initPendingNavigationLoader();

        $(document).ajaxSend(function (event, jqXHR, settings) {
            if (settings.global === false) {
                return;
            }
            if (++activeAjaxRequests === 1) {
                showLoader();
            }
        });

        $(document).ajaxComplete(function (event, jqXHR, settings) {
            if (settings.global === false) {
                return;
            }
            if (--activeAjaxRequests <= 0) {
                activeAjaxRequests = 0;
                hideLoader();
            }
        });

        $(document).on('click', 'a[href]', function (event) {
            var $link = $(this);
            if (shouldIgnoreLink($link)) {
                return;
            }
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }
            if (!isSameOriginNavigation($link.attr('href'))) {
                return;
            }

            markNavigationLoader();
        });

        window.addEventListener('pageshow', function () {
            navigationPending = false;
            activeAjaxRequests = 0;
            hideLoader(true);
        });

        $(function () {
            navigationPending = false;
            hideLoader();
        });
    }

    window.showGlobalLoader = function () {
        showLoader();
    };

    window.hideGlobalLoader = function (instant) {
        hideLoader(!!instant, true);
    };

    window.navigateWithLoader = function (url) {
        markNavigationLoader();
        window.location.href = url;
    };

    $(init);
})(jQuery);
