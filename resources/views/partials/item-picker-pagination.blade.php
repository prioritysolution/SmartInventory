@once
    <script>
        window.fetchItemPickerPage = function (url, filters, page, render, onError) {
            $('#itemPickerLoader').show();
            $('#itemPickerTableWrap').hide();
            $('#itemPickerPagination').hide();
            $('#itemPickerTable tbody').empty();
            $.get(url, Object.assign({}, filters, { page: page, page_size: 25 }))
                .done(render)
                .fail(function (xhr) {
                    $('#itemPickerLoader').hide();
                    if (onError) {
                        onError(xhr);
                    } else {
                        Swal.fire('Error', 'Failed to load items', 'error');
                    }
                });
        };

        window.renderItemPickerPagination = function (pagination, loadPage) {
            let $pager = $('#itemPickerPagination');
            if (!$pager.length) {
                $pager = $('<div id="itemPickerPagination" class="d-flex justify-content-between align-items-center mt-2"></div>')
                    .insertAfter('#itemPickerTableWrap');
            }

            const page = Number(pagination.page) || 1;
            const lastPage = Number(pagination.lastPage) || 1;
            const pageSize = Number(pagination.pageSize) || 25;
            const total = Number(pagination.total) || 0;
            const from = total ? (page - 1) * pageSize + 1 : 0;
            const to = Math.min(page * pageSize, total);
            let links = '';

            if (lastPage > 1) {
                links += `<li class="page-item ${page === 1 ? 'disabled' : ''}">
                    <a class="page-link item-picker-page-link" href="#" data-page="${page - 1}">Previous</a></li>`;
                const pages = new Set([1, lastPage]);
                for (let i = Math.max(1, page - 2); i <= Math.min(lastPage, page + 2); i++) {
                    pages.add(i);
                }
                let previousPage = 0;
                Array.from(pages).sort((a, b) => a - b).forEach(function (number) {
                    if (previousPage && number - previousPage > 1) {
                        links += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    links += `<li class="page-item ${page === number ? 'active' : ''}">
                        <a class="page-link item-picker-page-link" href="#" data-page="${number}">${number}</a></li>`;
                    previousPage = number;
                });
                links += `<li class="page-item ${page === lastPage ? 'disabled' : ''}">
                    <a class="page-link item-picker-page-link" href="#" data-page="${page + 1}">Next</a></li>`;
            }

            $pager.html(
                `<small class="text-muted">Showing ${from} to ${to} of ${total} items</small>` +
                (links ? `<ul class="pagination pagination-sm mb-0">${links}</ul>` : '')
            );
            $pager.toggle(total > 0);

            $(document).off('click.itemPickerPagination', '#itemPickerPagination .item-picker-page-link')
                .on('click.itemPickerPagination', '#itemPickerPagination .item-picker-page-link', function (event) {
                    event.preventDefault();
                    const $link = $(this);
                    if ($link.closest('.page-item').hasClass('disabled') ||
                        $link.closest('.page-item').hasClass('active')) {
                        return;
                    }
                    loadPage(Number($link.data('page')));
                });
        };

        $(document).on('hidden.bs.modal', '#itemPickerModal', function () {
            $('#itemPickerPagination').hide().empty();
        });

        $(document).on('change', '#modalCateId, #modalSubCateId', function () {
            $('#itemPickerPagination').hide().empty();
        });
    </script>
@endonce
