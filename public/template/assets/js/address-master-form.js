/**
 * Shared Address Master helpers for Agent / Member / Supplier / Customer forms.
 * Loads options from address-master/data/{type} using the live app base path.
 * PIN Code comes from the selected Post Office (mst_post.Pin_Code).
 */
window.AddressMasterForm = (function ($) {
    const TYPES = ['village', 'ps', 'post', 'dist'];
    const LABELS = {
        village: 'Village',
        ps: 'Police Station',
        post: 'Post Office',
        dist: 'District'
    };
    const cache = {};
    let loaded = false;
    let loading = null;
    let loadErrorShown = false;

    function resolveAppBase() {
        const meta = document.querySelector('meta[name="app-base"]');
        let configured = (meta && meta.content) || window.APP_BASE || '';
        try {
            if (configured) {
                const u = new URL(configured, window.location.href);
                if (u.origin === window.location.origin) {
                    return (u.origin + u.pathname).replace(/\/$/, '');
                }
                // APP_URL host mismatch (e.g. localhost) — keep path only.
                const pathOnly = (u.pathname || '').replace(/\/$/, '');
                if (pathOnly && pathOnly !== '/') {
                    return window.location.origin + pathOnly;
                }
            }
        } catch (e) {}

        const segs = window.location.pathname.split('/').filter(Boolean);
        if (segs.length >= 1 && /smartinventory/i.test(segs[0])) {
            return window.location.origin + '/' + segs[0];
        }
        return window.location.origin;
    }

    function apiUrl(path) {
        if (typeof window.siApiUrl === 'function') {
            return window.siApiUrl(path);
        }
        const base = resolveAppBase().replace(/\/$/, '');
        const clean = String(path || '').replace(/^\//, '');
        return base + '/' + clean;
    }

    function optionHtml(rows, type) {
        return (rows || []).map(r => {
            const name = $('<div>').text(r.Name ?? '').html();
            const pin = type === 'post' ? $('<div>').text(r.Pin_Code ?? '').html() : '';
            const pinAttr = type === 'post' ? ` data-pin-code="${pin}"` : '';
            return `<option value="${r.Id}"${pinAttr}>${name}</option>`;
        }).join('');
    }

    function syncPinFromPost(prefix) {
        prefix = prefix || '';
        const $post = $(`#${prefix}post_id`);
        const pin = $post.find('option:selected').data('pin-code') || '';
        $(`#${prefix}pin_code`).val(pin);
    }

    function loadAll() {
        if (loaded) return $.Deferred().resolve().promise();
        if (loading) return loading;

        const deferreds = TYPES.map(type =>
            $.ajax({
                url: apiUrl('address-master/data/' + type),
                method: 'GET',
                dataType: 'json',
                cache: true
            }).then(function (data) {
                cache[type] = Array.isArray(data) ? data : [];
                return cache[type];
            }, function () {
                cache[type] = cache[type] || [];
                return $.Deferred().reject(type);
            })
        );

        loading = $.when.apply($, deferreds).then(function () {
            loaded = true;
            fillSelects();
        }, function () {
            // Partial success still usable — mark loaded if any type has rows.
            const any = TYPES.some(t => (cache[t] || []).length > 0);
            if (any) {
                loaded = true;
                fillSelects();
                return;
            }
            loading = null;
            if (!loadErrorShown && typeof Swal !== 'undefined') {
                loadErrorShown = true;
                Swal.fire('Error', 'Failed to load address master options', 'error');
            }
            return $.Deferred().reject().promise();
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
                $sel.html(placeholder + optionHtml(cache[type] || [], type));
                if (current) $sel.val(String(current));
            });
        });
        $root.find('select.addr-master-select[data-addr-type="post"]').each(function () {
            const prefix = ($(this).attr('id') || 'post_id').replace(/post_id$/, '');
            syncPinFromPost(prefix);
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
            syncPinFromPost(prefix);
            if (values.pin_code && !$(`#${prefix}pin_code`).val()) {
                $(`#${prefix}pin_code`).val(values.pin_code);
            }
        });
    }

    function clearValues(prefix) {
        prefix = prefix || '';
        TYPES.forEach(type => $(`#${prefix}${type}_id`).val(''));
        $(`#${prefix}pin_code`).val('');
    }

    function collect(prefix) {
        prefix = prefix || '';
        const data = {};
        TYPES.forEach(type => {
            data[type + '_id'] = $(`#${prefix}${type}_id`).val() || '';
        });
        data.pin_code = $(`#${prefix}pin_code`).val() || '';
        return data;
    }

    function selectedName(prefix, type) {
        prefix = prefix || '';
        if (type === 'pin') {
            return $.trim($(`#${prefix}pin_code`).val() || '');
        }
        const $sel = $(`#${prefix}${type}_id`);
        const val = $sel.val();
        if (!val) return '';
        return $.trim($sel.find('option:selected').text());
    }

    function validate() {
        return true;
    }

    $(function () {
        window.APP_BASE = resolveAppBase();
        loadAll();
        $(document).on('change', 'select.addr-master-select[data-addr-type="post"]', function () {
            const prefix = ($(this).attr('id') || 'post_id').replace(/post_id$/, '');
            syncPinFromPost(prefix);
        });
    });

    return {
        loadAll,
        fillSelects,
        setValues,
        clearValues,
        collect,
        selectedName,
        validate,
        syncPinFromPost,
        apiUrl
    };
})(jQuery);
