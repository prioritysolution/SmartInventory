/**
 * Shared Select2 helper: searchable dropdowns everywhere,
 * AJAX + scroll paging for large agent/party lists.
 */
(function (window, $) {
    'use strict';

    if (!$) return;

    function defaultText(row) {
        const name = row.Party_Name || row.Agent_Name || row.text || '';
        const code = row.Party_Code || row.Agent_Code || row.code || '';
        if (code && name) {
            return name + ' (' + code + ')';
        }
        return code ? (name + '-' + code) : name;
    }

    function skipSelect($el) {
        if (!$el || !$el.length) return true;
        if ($el.hasClass('no-select2')) return true;
        if ($el.hasClass('addr-master-select')) return true;
        if ($el.attr('id') === 'rateSelect') return true;
        if ($el.closest('.dataTables_length, .dataTables_filter, .dt-length').length) return true;
        if ($el.is('[multiple]')) return true;
        if ($el.is(':hidden') && !$el.closest('.modal').length && $el.attr('id') !== 'rateSelect') {
            // hidden until shown — still init if it is a real form field
        }
        return false;
    }

    function dropdownParentFor($el) {
        const $modal = $el.closest('.modal');
        if ($modal.length) return $modal;
        return $(document.body);
    }

    function stampOptionMeta($el, data) {
        const raw = data && (data.raw || data);
        if (!raw) return;
        const val = $el.val();
        const $opt = $el.find('option[value="' + String(val).replace(/"/g, '\\"') + '"]');
        if (!$opt.length) return;
        if (raw.Agent_Name) $opt.attr('data-name', raw.Agent_Name);
        if (raw.Agent_Code) $opt.attr('data-code', raw.Agent_Code);
        if (raw.Party_Name) $opt.attr('data-name', raw.Party_Name);
        if (raw.Party_Code) $opt.attr('data-code', raw.Party_Code);
    }

    /**
     * @param {string|jQuery} selector
     * @param {object} opts
     */
    function initAjaxSelect2(selector, opts) {
        opts = opts || {};
        const $el = $(selector);
        if (!$el.length || typeof $el.select2 !== 'function') return $el;

        if ($el.data('select2')) {
            $el.select2('destroy');
        }

        if (!$el.find('option[value=""]').length && !$el.find('option[value="0"]').length) {
            $el.prepend('<option value=""></option>');
        }
        $el.find('option').not('[value=""], [value="0"]').remove();

        const allOption = opts.allOption || null;
        const cfg = {
            width: '100%',
            placeholder: opts.placeholder || 'Type to search...',
            allowClear: opts.allowClear !== false,
            minimumInputLength: 0,
            dropdownParent: opts.dropdownParent ? $(opts.dropdownParent) : dropdownParentFor($el),
            ajax: {
                url: opts.url,
                dataType: 'json',
                delay: 250,
                global: false,
                data: function (params) {
                    const extra = typeof opts.extraParams === 'function'
                        ? (opts.extraParams() || {})
                        : (opts.extraParams || {});
                    return Object.assign({
                        q: params.term || '',
                        page: params.page || 1,
                        page_size: opts.pageSize || 30,
                        party_type: opts.partyType || ''
                    }, extra);
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    const rows = (data && data.results) ? data.results : (Array.isArray(data) ? data : []);
                    const mapped = rows.map(function (row) {
                        if (typeof opts.mapResult === 'function') {
                            return opts.mapResult(row);
                        }
                        return {
                            id: row.id != null ? row.id : (row.Party_Id || row.Agent_Id || ''),
                            text: row.text != null ? row.text : defaultText(row),
                            raw: row
                        };
                    });
                    if (allOption && params.page === 1) {
                        const term = (params.term || '').toLowerCase();
                        if (!term || String(allOption.text).toLowerCase().indexOf(term) !== -1) {
                            mapped.unshift({
                                id: allOption.id,
                                text: allOption.text
                            });
                        }
                    }
                    return {
                        results: mapped,
                        pagination: {
                            more: !!(data && data.pagination && data.pagination.more)
                        }
                    };
                },
                cache: true
            }
        };

        $el.select2(cfg);
        $el.off('select2:select.siAjax').on('select2:select.siAjax', function (e) {
            stampOptionMeta($el, e.params && e.params.data);
        });
        $el.off('select2:open.siAjax').on('select2:open.siAjax', function () {
            setTimeout(function () {
                const $field = $('.select2-container--open .select2-search__field');
                if ($field.length) {
                    $field.trigger('input');
                }
            }, 0);
        });
        return $el;
    }

    function setAjaxSelect2Value(selector, id, text) {
        const $el = $(selector);
        if (!$el.length) return;
        const val = id == null || id === '' ? '' : String(id);
        if (!val || val === '0') {
            const zero = $el.find('option[value="0"]').length ? '0' : '';
            $el.val(zero || null).trigger('change');
            return;
        }
        if ($el.find('option[value="' + val.replace(/"/g, '\\"') + '"]').length === 0) {
            const opt = new Option(text || val, val, true, true);
            $el.append(opt);
        }
        $el.val(val).trigger('change');
    }

    function initLocalSelect2($el) {
        if (typeof $el.select2 !== 'function') return;
        if ($el.data('select2')) return;
        const placeholder = $el.find('option[value=""], option[value="0"]').first().text()
            || $el.data('placeholder')
            || 'Select...';
        $el.select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: $el.find('option[value=""]').length > 0,
            minimumResultsForSearch: 0,
            dropdownParent: dropdownParentFor($el)
        });
    }

    function isAgentSelect($el) {
        const id = $el.attr('id') || '';
        if ($el.hasClass('select2-agent')) return true;
        if ($el.data('siAjax') === 'agents') return true;
        if (id === 'agentId' || id === 'custAgentId') return true;
        return false;
    }

    function isPartySelect($el) {
        const id = $el.attr('id') || '';
        if ($el.data('siAjax') === 'parties') return true;
        if (id === 'partyId' || id === 'searchPartyId' || id === 'findPartyId') return true;
        return false;
    }

    function bindSelect2(el) {
        const $el = $(el);
        if (skipSelect($el)) return;
        if (typeof $el.select2 !== 'function') return;

        const urls = window.SI_SELECT2 || {};

        if (isAgentSelect($el) && urls.agents) {
            const hasAll = $el.find('option[value="0"]').length > 0;
            const placeholder = hasAll
                ? ($el.find('option[value="0"]').first().text() || 'All Agents')
                : ($el.find('option[value=""]').first().text() || 'Search agent...');
            initAjaxSelect2($el, {
                url: urls.agents,
                placeholder: placeholder,
                minLength: 0,
                pageSize: 30,
                allowClear: !hasAll,
                allOption: hasAll ? { id: '0', text: placeholder } : null
            });
            return;
        }

        if (isPartySelect($el) && urls.parties && !$el.data('select2')) {
            const partyType = $el.data('partyType') || $el.data('party-type') || '';
            const hasAll = $el.find('option[value="0"]').length > 0;
            const placeholder = $el.find('option:first').text() || 'Type to search...';
            initAjaxSelect2($el, {
                url: urls.parties,
                placeholder: placeholder,
                partyType: partyType,
                minLength: 0,
                pageSize: 30,
                allowClear: !hasAll,
                allOption: hasAll ? { id: '0', text: placeholder } : null
            });
            return;
        }

        if ($el.data('select2')) return;
        initLocalSelect2($el);
    }

    function initAllSelect2(root) {
        $(root || document).find('select').each(function () {
            bindSelect2(this);
        });
    }

    window.siAjaxSelect2 = initAjaxSelect2;
    window.siSetAjaxSelect2Value = setAjaxSelect2Value;
    window.siBindSelect2 = bindSelect2;
    window.siInitAllSelect2 = initAllSelect2;

    $(function () {
        setTimeout(function () {
            initAllSelect2(document);
        }, 0);
    });

    const origHtml = $.fn.html;
    $.fn.html = function (value) {
        if (arguments.length === 0) {
            return origHtml.call(this);
        }
        const $selects = this.filter('select');
        $selects.each(function () {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
        const ret = origHtml.apply(this, arguments);
        $selects.each(function () {
            bindSelect2(this);
        });
        return ret;
    };
})(window, window.jQuery);
