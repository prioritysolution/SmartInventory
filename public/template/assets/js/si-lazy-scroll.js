/**
 * Infinite scroll for search result tables.
 * Loads pages from the server as the user scrolls — never dumps the full set.
 *
 * Usage:
 *   const list = SiLazyScroll.create({
 *     scrollEl: '.find-sale-scroll',
 *     tbody: '#findSaleBody',
 *     url: '...',
 *     pageSize: 30,
 *     params: function () { return { from_date: ..., to_date: ... }; },
 *     renderRow: function (row, index) { return '<tr>...</tr>'; },
 *     emptyHtml: '<tr><td colspan="7">No records</td></tr>',
 *     colSpan: 7
 *   });
 *   list.resetAndLoad(); // on Search click
 */
(function (window, $) {
    'use strict';

    if (!$) return;

    function unwrap(payload) {
        if (Array.isArray(payload)) return payload;
        if (payload && Array.isArray(payload.results)) return payload.results;
        return [];
    }

    function create(opts) {
        opts = opts || {};
        const $scroll = $(opts.scrollEl);
        const $tbody = $(opts.tbody);
        const pageSize = Math.max(10, Math.min(100, opts.pageSize || 40));
        const showEndRow = opts.showEndRow !== false;
        let offset = 0;
        let loading = false;
        let done = false;
        let total = null;
        let reqToken = 0;

        function colSpan() {
            if (typeof opts.colSpan === 'function') {
                return opts.colSpan() || 7;
            }
            return opts.colSpan || 7;
        }

        function loadingRow() {
            return '<tr class="si-lazy-loading"><td colspan="' + colSpan() +
                '" class="text-center text-muted py-2">Loading more...</td></tr>';
        }

        function endRow(count) {
            if (count === 0) {
                return opts.emptyHtml ||
                    ('<tr><td colspan="' + colSpan() + '" class="text-center text-muted">No records found</td></tr>');
            }
            if (!showEndRow) return '';
            return '<tr class="si-lazy-end"><td colspan="' + colSpan() +
                '" class="text-center text-muted small py-2">End of results' +
                (total != null ? ' (' + total + ')' : '') + '</td></tr>';
        }

        function clearMarkers() {
            $tbody.find('tr.si-lazy-loading, tr.si-lazy-end').remove();
        }

        function loadMore(reset) {
            if (loading) return;
            if (!reset && done) return;

            if (reset) {
                offset = 0;
                done = false;
                total = null;
                $tbody.html(loadingRow());
            } else {
                clearMarkers();
                $tbody.append(loadingRow());
            }

            loading = true;
            const token = ++reqToken;
            const params = $.extend({}, typeof opts.params === 'function' ? opts.params() : (opts.params || {}), {
                limit: pageSize,
                offset: offset
            });

            $.get(opts.url, params)
                .done(function (payload) {
                    if (token !== reqToken) return;

                    const rows = unwrap(payload);
                    if (payload && !Array.isArray(payload) && payload.recordsTotal != null) {
                        total = Number(payload.recordsTotal);
                    }
                    if (payload && !Array.isArray(payload) && payload.has_more === false) {
                        done = true;
                    }

                    clearMarkers();
                    if (reset) {
                        $tbody.empty();
                    }

                    if (!rows.length) {
                        if (offset === 0) {
                            $tbody.html(endRow(0));
                        } else if (showEndRow) {
                            $tbody.append(endRow(offset));
                        }
                        done = true;
                        if (typeof opts.onChunk === 'function') {
                            opts.onChunk(rows, payload, reset, { total: total, offset: offset, done: true });
                        }
                        return;
                    }

                    if (typeof opts.renderRow === 'function') {
                        let html = '';
                        rows.forEach(function (row, i) {
                            html += opts.renderRow(row, offset + i);
                        });
                        $tbody.append(html);
                    }
                    offset += rows.length;

                    const hasMore = payload && typeof payload.has_more === 'boolean'
                        ? payload.has_more
                        : (rows.length >= pageSize);
                    done = !hasMore;
                    if (done && showEndRow) {
                        $tbody.append(endRow(offset));
                    }

                    if (typeof opts.onChunk === 'function') {
                        opts.onChunk(rows, payload, reset, { total: total, offset: offset, done: done });
                    }
                })
                .fail(function (xhr) {
                    if (token !== reqToken) return;
                    clearMarkers();
                    const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed to load';
                    if (offset === 0) {
                        $tbody.html('<tr><td colspan="' + colSpan() +
                            '" class="text-center text-danger">' + $('<div>').text(msg).html() + '</td></tr>');
                    }
                    if (typeof opts.onError === 'function') opts.onError(xhr);
                })
                .always(function () {
                    if (token === reqToken) loading = false;
                });
        }

        function onScroll() {
            if (loading || done) return;
            const el = $scroll[0];
            if (!el) return;
            if (el.scrollTop + el.clientHeight >= el.scrollHeight - 80) {
                loadMore(false);
            }
        }

        $scroll.off('scroll.siLazy').on('scroll.siLazy', onScroll);

        return {
            resetAndLoad: function () { loadMore(true); },
            loadMore: function () { loadMore(false); },
            destroy: function () {
                $scroll.off('scroll.siLazy');
                reqToken++;
            }
        };
    }

    window.SiLazyScroll = {
        create: create,
        unwrap: unwrap
    };
})(window, jQuery);
