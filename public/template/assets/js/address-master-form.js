/**
 * Shared Address Master helpers for Agent / Member / Supplier / Customer forms.
 * Loads options from /address-master/data/{type}.
 */
window.AddressMasterForm = (function ($) {
    const TYPES = ['village', 'ps', 'post', 'pin', 'dist'];
    const LABELS = {
        village: 'Village',
        ps: 'Police Station',
        post: 'Post Office',
        pin: 'PIN Code',
        dist: 'District'
    };
    const cache = {};
    let loaded = false;
    let loading = null;

    function optionHtml(rows) {
        return (rows || []).map(r =>
            `<option value="${r.Id}">${$('<div>').text(r.Name ?? '').html()}</option>`
        ).join('');
    }

    function loadAll() {
        if (loaded) return $.Deferred().resolve().promise();
        if (loading) return loading;

        const requests = TYPES.map(type =>
            $.get(`/address-master/data/${type}`).then(data => {
                cache[type] = data || [];
            })
        );

        loading = $.when.apply($, requests).then(function () {
            loaded = true;
            fillSelects();
        }).fail(function () {
            loading = null;
            Swal.fire('Error', 'Failed to load address master options', 'error');
        });

        return loading;
    }

    function fillSelects(root) {
        const $root = root ? $(root) : $(document);
        TYPES.forEach(type => {
            $root.find(`select.addr-master-select[data-addr-type="${type}"]`).each(function () {
                const $sel = $(this);
                const current = $sel.val();
                const placeholder = $sel.find('option:first').prop('outerHTML') ||
                    `<option value="">Select ${LABELS[type]}</option>`;
                $sel.html(placeholder + optionHtml(cache[type] || []));
                if (current) $sel.val(String(current));
            });
        });
    }

    function setValues(prefix, values) {
        prefix = prefix || '';
        values = values || {};
        return loadAll().then(function () {
            TYPES.forEach(type => {
                const key = type + '_id';
                const val = values[key] ?? values[type] ?? '';
                $(`#${prefix}${key}`).val(val ? String(val) : '');
            });
        });
    }

    function clearValues(prefix) {
        prefix = prefix || '';
        TYPES.forEach(type => $(`#${prefix}${type}_id`).val(''));
    }

    function collect(prefix) {
        prefix = prefix || '';
        const data = {};
        TYPES.forEach(type => {
            data[type + '_id'] = $(`#${prefix}${type}_id`).val() || '';
        });
        return data;
    }

    function selectedName(prefix, type) {
        prefix = prefix || '';
        const $sel = $(`#${prefix}${type}_id`);
        const val = $sel.val();
        if (!val) return '';
        return $.trim($sel.find('option:selected').text());
    }

    function validate(prefix) {
        prefix = prefix || '';
        for (let i = 0; i < TYPES.length; i++) {
            const type = TYPES[i];
            if (!$(`#${prefix}${type}_id`).val()) {
                Swal.fire('Validation Error', LABELS[type] + ' is required', 'error');
                return false;
            }
        }
        return true;
    }

    $(function () {
        loadAll();
    });

    return {
        loadAll,
        fillSelects,
        setValues,
        clearValues,
        collect,
        selectedName,
        validate
    };
})(jQuery);
