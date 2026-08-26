(function ($) {
    var originalVal = $.fn.val;
    var applyingVal = false;

    function parseDateParts(value) {
        if (value === undefined || value === null || value === '') {
            return null;
        }
        var raw = String(value).trim();
        var iso = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (iso) {
            return { y: iso[1], m: iso[2], d: iso[3] };
        }
        var dmySlash = raw.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        if (dmySlash) {
            return { y: dmySlash[3], m: dmySlash[2], d: dmySlash[1] };
        }
        var dmyDash = raw.match(/^(\d{2})-(\d{2})-(\d{4})/);
        if (dmyDash) {
            return { y: dmyDash[3], m: dmyDash[2], d: dmyDash[1] };
        }
        return false;
    }

    function isoToDmy(value) {
        var parts = parseDateParts(value);
        if (parts === null) {
            return '';
        }
        if (!parts) {
            return String(value);
        }
        return parts.d + '/' + parts.m + '/' + parts.y;
    }

    function isoToDisplay(value) {
        var parts = parseDateParts(value);
        if (parts === null) {
            return '';
        }
        if (!parts) {
            return String(value);
        }
        return parts.d + '-' + parts.m + '-' + parts.y;
    }

    function dmyToIso(value) {
        var parts = parseDateParts(value);
        if (parts === null) {
            return '';
        }
        if (!parts) {
            return String(value);
        }
        return parts.y + '-' + parts.m + '-' + parts.d;
    }

    function parseBound(value) {
        if (!value) {
            return false;
        }
        var raw = String(value).trim().substring(0, 10);
        if (!window.moment) {
            return raw;
        }
        var parsed = moment(raw, ['YYYY-MM-DD', 'DD/MM/YYYY', 'DD-MM-YYYY'], true);
        return parsed.isValid() ? parsed : false;
    }

    function convertIsoDatesDeep(data) {
        if (data === undefined || data === null) {
            return data;
        }
        if (typeof data === 'string') {
            if (/^\d{4}-\d{2}-\d{2}(?:[ T].*)?$/.test(data.trim())) {
                return isoToDmy(data);
            }
            data = data.replace(/"(\d{4})-(\d{2})-(\d{2})(?:[T ][^"]*)?"/g, function (_, y, m, d) {
                return '"' + d + '/' + m + '/' + y + '"';
            });
            return data.replace(/=(\d{4})-(\d{2})-(\d{2})(?=&|$)/g, function (_, y, m, d) {
                return '=' + d + '/' + m + '/' + y;
            });
        }
        if (typeof data !== 'object') {
            return data;
        }
        if (typeof Blob !== 'undefined' && data instanceof Blob) {
            return data;
        }
        if (window.FormData && data instanceof FormData) {
            var formData = new FormData();
            data.forEach(function (value, key) {
                formData.append(key, typeof value === 'string' ? convertIsoDatesDeep(value) : value);
            });
            return formData;
        }
        if (Array.isArray(data)) {
            return data.map(function (item) {
                return convertIsoDatesDeep(item);
            });
        }
        var copy = {};
        Object.keys(data).forEach(function (key) {
            copy[key] = convertIsoDatesDeep(data[key]);
        });
        return copy;
    }

    function getPicker($el) {
        return $el.data('DateTimePicker') || $el.closest('.si-date-wrap').data('DateTimePicker');
    }

    function injectStyles() {
        if (document.getElementById('si-date-styles')) {
            return;
        }
        var css = [
            '.si-date-wrap { width: 100%; position: relative; z-index: 5; }',
            '.si-date-wrap .input-group-text { cursor: pointer; background: #fff; }',
            '.si-date-wrap input.si-dmy { background-image: none !important; cursor: text; }',
            '.si-date-wrap input.si-dmy::-webkit-calendar-picker-indicator { display: none !important; }',
            '.bootstrap-datetimepicker-widget { z-index: 20000 !important; }',
            '.bootstrap-datetimepicker-widget.dropdown-menu { margin: 0 !important; }',
            '.modal .bootstrap-datetimepicker-widget { z-index: 20050 !important; }'
        ].join('');
        $('<style id="si-date-styles">').text(css).appendTo('head');
    }

    $.fn.val = function (value) {
        if (!this.length || applyingVal) {
            return originalVal.apply(this, arguments);
        }

        if (arguments.length === 0) {
            if (this.length === 1 && this.hasClass('si-dmy')) {
                return dmyToIso(originalVal.call(this));
            }
            return originalVal.apply(this, arguments);
        }

        var args = arguments;
        return this.each(function () {
            var $el = $(this);
            if (!$el.hasClass('si-dmy')) {
                originalVal.apply($el, args);
                return;
            }

            var mapped = (value === undefined || value === null || value === '') ? '' : isoToDmy(value);
            applyingVal = true;
            try {
                var picker = getPicker($el);
                if (picker && typeof picker.date === 'function') {
                    picker.date(mapped ? moment(mapped, ['DD/MM/YYYY', 'YYYY-MM-DD']) : null);
                } else {
                    originalVal.call($el, mapped);
                }
            } catch (e) {
                originalVal.call($el, mapped);
            } finally {
                applyingVal = false;
            }
        });
    };

    function placeWidget($input) {
        var $anchor = $input.closest('.si-date-wrap');
        if (!$anchor.length) {
            $anchor = $input;
        }
        var $widget = $('.bootstrap-datetimepicker-widget:visible').last();
        if (!$widget.length) {
            $widget = $('.bootstrap-datetimepicker-widget').last();
        }
        if (!$widget.length || !$anchor[0]) {
            return;
        }
        var rect = $anchor[0].getBoundingClientRect();
        var widgetHeight = $widget.outerHeight() || 280;
        var widgetWidth = $widget.outerWidth() || 280;
        var top = rect.bottom + 4;
        var left = rect.left;
        if (top + widgetHeight > window.innerHeight - 8) {
            top = Math.max(8, rect.top - widgetHeight - 4);
        }
        if (left + widgetWidth > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - widgetWidth - 8);
        }
        $widget.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px',
            right: 'auto',
            bottom: 'auto',
            display: 'block',
            zIndex: 20000
        });
    }

    function wrapWithCalendar($el) {
        if ($el.closest('.si-date-wrap').length) {
            return $el.closest('.si-date-wrap');
        }

        var $wrap = $('<div class="input-group si-date-wrap"></div>');
        $el.after($wrap);
        $wrap.append($el);
        $wrap.append(
            '<span class="input-group-text input-group-addon si-date-icon datepickerbutton" title="Select date">' +
                '<i class="fa-solid fa-calendar" aria-hidden="true"></i>' +
            '</span>'
        );
        return $wrap;
    }

    function initDateInputs(scope) {
        injectStyles();
        $(scope || document).find('input[type="date"], input.si-dmy').each(function () {
            var $el = $(this);
            if ($el.data('siDmyReady')) {
                return;
            }

            // Hidden fields (e.g. FMCG pack date) must init after they are shown,
            // otherwise the calendar widget never opens.
            if (!$el.is(':visible')) {
                return;
            }

            var currentIso = $el.attr('value') || originalVal.call($el) || '';
            var min = $el.attr('min');
            var max = $el.attr('max');
            var isReadonly = $el.prop('readonly') || $el.prop('disabled');

            $el.attr('type', 'text');
            $el.addClass('si-dmy');
            $el.attr('placeholder', 'DD/MM/YYYY');
            $el.attr('autocomplete', 'off');
            originalVal.call($el, isoToDmy(currentIso));

            var $wrap = wrapWithCalendar($el);
            $el.data('siDmyReady', true);

            if (typeof $el.datetimepicker !== 'function') {
                return;
            }

            var options = {
                format: 'DD/MM/YYYY',
                extraFormats: ['DD/MM/YYYY', 'YYYY-MM-DD', 'DD-MM-YYYY'],
                useCurrent: false,
                allowInputToggle: false,
                focusOnShow: false,
                keepInvalid: true,
                ignoreReadonly: true,
                showTodayButton: true,
                datepickerInput: 'input.si-dmy',
                keyBinds: {
                    down: function () {},
                    up: function () {},
                    left: function () {},
                    right: function () {},
                    delete: function () {},
                    enter: function () {},
                    escape: function () {
                        this.hide();
                    }
                },
                widgetParent: $('body'),
                widgetPositioning: {
                    horizontal: 'auto',
                    vertical: 'bottom'
                },
                icons: {
                    time: 'fa fa-clock',
                    date: 'fa fa-calendar',
                    up: 'fa fa-angle-up',
                    down: 'fa fa-angle-down',
                    previous: 'fa fa-angle-left',
                    next: 'fa fa-angle-right',
                    today: 'fa fa-crosshairs',
                    clear: 'fa fa-trash',
                    close: 'fa fa-times'
                }
            };

            var minDate = parseBound(min);
            var maxDate = parseBound(max);
            if (minDate) {
                options.minDate = minDate;
            }
            if (maxDate) {
                options.maxDate = maxDate;
            }

            // Init on the wrap, never the input. This plugin always opens on
            // input focus when attached to the <input> itself.
            try {
                $wrap.datetimepicker(options);
            } catch (e) {}

            $wrap.find('.si-date-icon, .input-group-addon').off('click mousedown');
            $el.off('keydown keyup keypress');

            var picker = getPicker($el);
            if (picker && currentIso) {
                try {
                    picker.date(moment(isoToDmy(currentIso), ['DD/MM/YYYY', 'YYYY-MM-DD']));
                } catch (e) {}
            }

            if (picker && isReadonly) {
                $wrap.find('.input-group-addon').css('pointer-events', 'none');
            }

            // Plugin binds change with stopImmediatePropagation; replace so page handlers still run.
            $el.off('change');
            $el.on('blur.siDateParse change.siDateParse', function () {
                if ($el.data('siSyncing')) {
                    return;
                }
                var activePicker = getPicker($el);
                if (!activePicker) {
                    return;
                }
                var display = String(originalVal.call($el) || '').trim();
                applyingVal = true;
                try {
                    if (!display) {
                        activePicker.date(null);
                    } else {
                                        var parsed = moment(display, ['DD/MM/YYYY', 'YYYY-MM-DD', 'DD-MM-YYYY'], true);
                        if (parsed.isValid()) {
                            activePicker.date(parsed);
                            originalVal.call($el, parsed.format('DD/MM/YYYY'));
                        }
                    }
                } catch (e) {}
                applyingVal = false;
            });

            function notifyChange() {
                if ($el.data('siSyncing')) {
                    return;
                }
                $el.data('siSyncing', true);
                $el.trigger('change');
                $el.data('siSyncing', false);
            }

            $wrap.on('dp.change', notifyChange);
            $el.on('dp.change', notifyChange);
            $wrap.on('dp.show', function () {
                $('.si-date-wrap').removeClass('si-picker-open');
                $wrap.addClass('si-picker-open');
                placeWidget($el);
                setTimeout(function () {
                    placeWidget($el);
                }, 50);
            });
            $wrap.on('dp.hide', function () {
                $wrap.removeClass('si-picker-open');
            });
        });
    }

    function togglePickerFromEvent(e) {
        e.preventDefault();
        e.stopPropagation();
        var $target = $(e.currentTarget);
        var $wrap = $target.closest('.si-date-wrap');
        var $input = $wrap.find('input.si-dmy, input[type="date"]').first();
        if (!$input.length) {
            return;
        }
        if (!$input.data('siDmyReady')) {
            initDateInputs($wrap);
        }
        var picker = getPicker($input);
        if (!picker) {
            return;
        }
        if ($wrap.hasClass('si-picker-open') && $('.bootstrap-datetimepicker-widget:visible').length) {
            if (typeof picker.hide === 'function') {
                picker.hide();
            }
            $wrap.removeClass('si-picker-open');
            return;
        }
        $('.si-date-wrap').each(function () {
            var other = $(this).data('DateTimePicker');
            if (other && typeof other.hide === 'function' && this !== $wrap[0]) {
                other.hide();
            }
        });
        if (typeof picker.show === 'function') {
            picker.show();
            $wrap.addClass('si-picker-open');
            placeWidget($input);
            setTimeout(function () {
                placeWidget($input);
            }, 50);
        }
    }

    $(document).on('click.siDate', '.si-date-wrap .si-date-icon, .si-date-wrap .input-group-addon, .si-date-wrap .input-group-text', togglePickerFromEvent);

    $(document).on('mousedown.siDateHide', function (e) {
        var $t = $(e.target);
        if ($t.closest('.bootstrap-datetimepicker-widget').length) {
            return;
        }
        if ($t.closest('.si-date-wrap .si-date-icon, .si-date-wrap .input-group-addon, .si-date-wrap .input-group-text').length) {
            return;
        }
        $('.si-date-wrap').each(function () {
            var picker = $(this).data('DateTimePicker');
            if (picker && typeof picker.hide === 'function') {
                picker.hide();
            }
        });
    });

    window.siDate = {
        toDisplay: isoToDisplay,
        toInput: isoToDmy,
        toIso: dmyToIso,
        init: initDateInputs
    };

    $(function () {
        initDateInputs(document);
    });

    $(document).on('shown.bs.modal', function (e) {
        initDateInputs(e.target);
    });

    $.ajaxPrefilter(function (options) {
        if (options.data) {
            options.data = convertIsoDatesDeep(options.data);
        }
    });
})(jQuery);
