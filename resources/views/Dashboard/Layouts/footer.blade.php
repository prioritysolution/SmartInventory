<!-- jQuery -->
<script src="{{asset('template/assets/js/jquery-3.7.1.min.js')}}"></script>


<!-- Bootstrap Core JS -->
<script src="{{asset('template/assets/js/bootstrap.bundle.min.js')}}"></script> 

<!-- Daterangepicker JS -->
<script src="{{asset('template/assets/js/moment.js')}}"></script>
<script src="{{asset('template/assets/plugins/daterangepicker/daterangepicker.js')}}"></script>

<!-- Simplebar JS -->
<script src="{{asset('template/assets/plugins/simplebar/simplebar.min.js')}}"></script>

<!-- Datetimepicker JS -->
<script src="{{asset('template/assets/js/bootstrap-datetimepicker.min.js')}}"></script>

<!-- Chart JS -->
<script src="{{asset('template/assets/plugins/apexchart/apexcharts.min.js')}}"></script>
<script src="{{asset('template/assets/plugins/apexchart/chart-data.js')}}"></script>

<!-- Datatable JS -->
<script src="{{asset('template/assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('template/assets/js/dataTables.bootstrap5.min.js')}}"></script>

<!-- SweetAlert -->
<script src="{{asset('template/assets/plugins/sweetalert/sweetalert2.all.min.js')}}"></script>

<!-- Menu Search -->
<script src="{{asset('template/assets/js/MenuSearch/menu-search.js')}}"></script>




<!-- Custom JS -->
<script src="{{asset('template/assets/js/script.js')}}"></script>
<script src="{{asset('template/assets/js/global-loader.js')}}?v=2"></script>
<script src="{{asset('template/assets/js/date-format.js')}}?v=16"></script>
<script src="{{asset('template/assets/js/print-helper.js')}}?v=1"></script>

<script>
if (!IS_ADMIN) {
    function denyMasterSave(e) {
        e.preventDefault();
        e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        Swal.fire('Access Denied', 'You do not have permission to perform this action.', 'warning');
        return false;
    }

    // Allow Add / Save. Block only Update (edit) and delete.
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button, a.btn, [role="button"]');
        if (!btn) return;
        const label = (btn.innerText || btn.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
        if (label !== 'update') return;
        denyMasterSave(e);
    }, true);

    $(window).on('load', function () {
        $(document).on('click', '.deleteRow', function (e) {
            return denyMasterSave(e);
        });
    });
}
</script>

<script>
    // Prevent mouse scroll from changing number input values globally
    $(document).on('wheel', 'input[type="number"]', function (e) {
        $(this).blur();
    });
</script>

<script>
    // Modals close only via X / Cancel — not outside click or Esc
    $(function () {
        $('.modal').attr({
            'data-bs-backdrop': 'static',
            'data-bs-keyboard': 'false'
        });
    });
    $(document).on('show.bs.modal', '.modal', function () {
        $(this).attr({
            'data-bs-backdrop': 'static',
            'data-bs-keyboard': 'false'
        });
        const modal = bootstrap.Modal.getInstance(this);
        if (modal) {
            modal._config.backdrop = 'static';
            modal._config.keyboard = false;
        }
    });
</script>
