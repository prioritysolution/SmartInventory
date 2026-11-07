</div>

<script src="{{asset('agenttemplate/assets/js/jquery-3.6.0.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/feather.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/jquery.slimscroll.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('agenttemplate/assets/js/dataTables.bootstrap4.min.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/bootstrap.bundle.min.js')}}"></script>

<script src="{{asset('template/assets/js/moment.js')}}"></script>
<script src="{{asset('template/assets/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{asset('template/assets/js/date-format.js')}}?v=18"></script>
<script src="{{asset('template/assets/js/si-lazy-scroll.js')}}?v=2"></script>

<script src="{{asset('agenttemplate/assets/plugins/apexchart/apexcharts.min.js')}}"></script>
<script src="{{asset('agenttemplate/assets/plugins/apexchart/chart-data.js')}}"></script>

<script src="{{asset('agenttemplate/assets/js/script.js')}}"></script>
<script src="{{asset('template/assets/js/global-loader.js')}}?v=2"></script>
<script src="{{asset('template/assets/js/print-helper.js')}}?v=1"></script>
<script src="{{asset('template/assets/js/report-download-helper.js')}}?v=1"></script>
<script src="{{asset('template/assets/js/disable-autocomplete.js')}}?v=1"></script>
<script src="{{asset('template/assets/js/required-field-feedback.js')}}?v=1"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.SI_SELECT2 = {
        parties: @json(route('agent.parties.search'))
    };
</script>
<script src="{{asset('template/assets/js/ajax-select2.js')}}?v=3"></script>
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
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getInstance(this);
            if (modal) {
                modal._config.backdrop = 'static';
                modal._config.keyboard = false;
            }
        }
    });
</script>