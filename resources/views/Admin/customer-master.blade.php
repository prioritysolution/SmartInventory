@extends('Dashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h6 class=" ps-2">Customer Master</h6>
        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
            <a href="javascript:void(0);" class="btn btn-primary d-flex align-items-center" onclick="openAddModal()">
                + Add New
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="customerTable" class="table table-nowrap">
                    <thead class="thead-light">
                        <tr>
                            <th>Sl</th>
                            <th>Party Code</th>
                            <th>Party Name</th>
                            <th>Mobile No</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
<div class="modal-dialog modal-xl" style="max-width: 95%;">
<div class="modal-content" style="min-height: 60vh;">

<div class="modal-header">
    <h5 class="modal-title" id="modalTitle">Add New Customer</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" id="partyId">
<div class="row g-4">
    <div class="col-md-3 mb-3">
        <label class="form-label">Party Name<span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-lg" id="partyName" maxlength="250" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Select Agent</label>
        <select class="form-select form-control-lg" id="custAgentId" data-si-ajax="agents">
            <option value="">Select Agent</option>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Mobile No</label>
        <input type="text" class="form-control form-control-lg" id="mobileNo" maxlength="10" autocomplete="off"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Whatsapp No</label>
        <input type="text" class="form-control form-control-lg" id="altMobileNo" maxlength="10" autocomplete="off"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Mail Id</label>
        <input type="email" class="form-control form-control-lg" id="mailId" maxlength="50" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Address 1</label>
        <input type="text" class="form-control form-control-lg" id="address1" maxlength="200" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Address 2</label>
        <input type="text" class="form-control form-control-lg" id="address2" maxlength="200" autocomplete="off">
    </div>
    @include('Admin.partials.address-master-fields', ['required' => false])
    <div class="col-md-3 mb-3">
        <label class="form-label">City</label>
        <input type="text" class="form-control form-control-lg" id="city" maxlength="50" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">State</label>
        <input type="text" class="form-control form-control-lg" id="state" maxlength="25" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Pan No</label>
        <input type="text" class="form-control form-control-lg" id="panNo" maxlength="25" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">GSTIN</label>
        <input type="text" class="form-control form-control-lg" id="gstin" maxlength="25" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">State Code</label>
        <input type="text" class="form-control form-control-lg" id="stateCode" maxlength="2" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Credit Limit</label>
        <input type="number" step="0.01" class="form-control form-control-lg" id="creditLimit" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Opening Balance</label>
        <input type="number" step="0.01" min="0" class="form-control form-control-lg" id="openingBalance" autocomplete="off">
    </div>
</div>
</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" id="saveCustomer">Save</button>
</div>

</div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('template/assets/js/address-master-form.js') }}?v=5"></script>
<script>
(function () {
    function apiUrl(path) {
        if (typeof window.siApiUrl === 'function') {
            return window.siApiUrl(path);
        }
        const base = String(window.APP_BASE || '').replace(/\/$/, '');
        const clean = String(path || '').replace(/^\//, '');
        return base ? (base + '/' + clean) : ('/' + clean);
    }

    let customerTable = null;

    $(document).ready(function () {
        function initCustomerTable() {
            const $table = $('#customerTable').removeClass('datatable');
            if ($.fn.DataTable.isDataTable($table)) {
                try { $table.DataTable().destroy(); } catch (e) {}
            }

            customerTable = $table.DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                pageLength: 25,
                order: [],
                destroy: true,
                autoWidth: false,
                ajax: {
                    url: apiUrl('customer-master/list'),
                    type: 'GET',
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to load customers';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', msg, 'error');
                        }
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'Party_Code', defaultContent: '' },
                    { data: 'Party_Name', defaultContent: '' },
                    { data: 'Contact_No', defaultContent: '' },
                    { data: 'Address_Line1', defaultContent: '' },
                    {
                        data: 'Status_Cd',
                        defaultContent: 1,
                        render: function (v) { return Number(v) === 1 ? 'Active' : 'Inactive'; }
                    },
                    {
                        data: 'Party_Id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function (id, type, row) {
                            return '<button type="button" class="btn btn-primary btn-sm editRow me-1" data-id="' + id + '">Edit</button>' +
                                '<button type="button" class="btn btn-danger btn-sm deleteRow" data-id="' + id + '" data-name="' +
                                $('<div>').text(row.Party_Name || '').html() + '">Delete</button>';
                        }
                    }
                ],
                language: {
                    processing: 'Loading customers...',
                    emptyTable: 'No customers found',
                    zeroRecords: 'No matching customers',
                    search: '',
                    searchPlaceholder: 'Search...',
                    sLengthMenu: 'Row Per Page _MENU_ Entries',
                    info: '_START_ - _END_ of _TOTAL_ items',
                    paginate: {
                        next: '<i class="isax isax-arrow-right-1"></i>',
                        previous: '<i class="isax isax-arrow-left"></i>'
                    }
                }
            });
        }

        // Run after script.js's immediate `.datatable` init (footer loads before stacked scripts).
        setTimeout(initCustomerTable, 0);

        $(document).on('click', '.editRow', function () {
            const id = $(this).data('id');
            $.get(apiUrl('customer-master/details/' + id))
                .done(function (c) {
                    $('#partyId').val(c.Party_Id);
                    $('#partyName').val(c.Party_Name || '');
                    const agentId = c.Cust_Agent_Id || '';
                    const agentLabel = (c.Agent_Name || '') + (c.Agent_Code ? ' (' + c.Agent_Code + ')' : '');
                    if (typeof siSetAjaxSelect2Value === 'function') {
                        siSetAjaxSelect2Value('#custAgentId', agentId, agentLabel || agentId);
                    } else {
                        $('#custAgentId').val(agentId).trigger('change');
                    }
                    $('#mobileNo').val(c.Contact_No || '');
                    $('#altMobileNo').val(c.AltContact_No || '');
                    $('#mailId').val(c.EMail || '');
                    $('#address1').val(c.Address_Line1 || '');
                    $('#address2').val(c.Address_Line2 || '');
                    $('#city').val(c.City || '');
                    $('#state').val(c.State || '');
                    AddressMasterForm.setValues('', {
                        village_id: c.Village_Id || '',
                        ps_id: c.Ps_Id || '',
                        post_id: c.Post_Id || '',
                        dist_id: c.Dist_Id || '',
                        pin_code: c.Pin_Code || ''
                    });
                    $('#panNo').val(c.Pan_No || '');
                    $('#gstin').val(c.GstIn || '');
                    $('#stateCode').val(c.State_Cd || '');
                    $('#creditLimit').val(c.Credit_Limit || '');
                    $('#openingBalance').val(c.Opening_Bal || '');
                    $('#modalTitle').text('Edit Customer');
                    $('#saveCustomer').prop('disabled', false).text('Update');
                    $('#customerModal').modal('show');
                })
                .fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to load customer', 'error');
                });
        });

        $(document).on('click', '.deleteRow', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            Swal.fire({
                title: 'Delete Customer?',
                text: name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete'
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: apiUrl('customer-master/' + id),
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                    success: res => {
                        Swal.fire('Deleted', res.message, 'success');
                        if (customerTable) customerTable.ajax.reload(null, false);
                    },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete', 'error')
                });
            });
        });

        $('#saveCustomer').click(function () {
            if (!validateForm()) return;

            $(this).prop('disabled', true).text('Saving...');

            const partyId = $('#partyId').val();
            const url = partyId ? apiUrl('customer-master/' + partyId) : apiUrl('customer-master/save');

            const data = Object.assign({
                _token: "{{ csrf_token() }}",
                party_name:   $('#partyName').val(),
                mobile_no:    $('#mobileNo').val(),
                alt_mobile_no: $('#altMobileNo').val(),
                mail_id:      $('#mailId').val(),
                address1:     $('#address1').val(),
                address2:     $('#address2').val(),
                city:         $('#city').val(),
                district:     AddressMasterForm.selectedName('', 'dist'),
                state:        $('#state').val(),
                pin_code:     AddressMasterForm.selectedName('', 'pin'),
                pan_no:       $('#panNo').val(),
                gstin:        $('#gstin').val(),
                state_code:   $('#stateCode').val(),
                credit_limit: $('#creditLimit').val(),
                opening_balance: $('#openingBalance').val(),
                cust_agent_id: $('#custAgentId').val() || 0
            }, AddressMasterForm.collect(''));

            if (partyId) data._method = 'PUT';

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (res) {
                    $('#customerModal').modal('hide');
                    const msg = res.message + (res.party_code ? '<br><b>Party Code: ' + res.party_code + '</b>' : '');
                    Swal.fire({ title: 'Success', html: msg, icon: 'success' });
                    if (customerTable) customerTable.ajax.reload(null, false);
                },
                error: function (xhr) {
                    $('#saveCustomer').prop('disabled', false).text(partyId ? 'Update' : 'Save');
                    if (xhr.status === 422) {
                        const message = xhr.responseJSON?.errors
                            ? Object.values(xhr.responseJSON.errors)[0][0]
                            : (xhr.responseJSON?.error || 'Validation failed');
                        Swal.fire('Validation Error', message, 'error');
                    } else {
                        Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save customer', 'error');
                    }
                }
            });
        });
    });

    window.validateForm = function () {
        if (!$('#partyName').val()) { Swal.fire('Validation Error', 'Party Name required', 'error'); return false; }
        if ($('#mobileNo').val() && !/^\d{10}$/.test($('#mobileNo').val())) { Swal.fire('Validation Error', 'Mobile No must be 10 digits', 'error'); return false; }
        return true;
    };

    window.openAddModal = function () {
        $('#partyId').val('');
        $('#partyName, #mobileNo, #altMobileNo, #mailId').val('');
        if (typeof siSetAjaxSelect2Value === 'function') {
            siSetAjaxSelect2Value('#custAgentId', '', '');
        } else {
            $('#custAgentId').val('').trigger('change');
        }
        $('#address1, #address2, #city, #state').val('');
        AddressMasterForm.clearValues('');
        $('#panNo, #gstin, #creditLimit, #openingBalance').val('');
        $('#stateCode').val('19');
        $('#modalTitle').text('Add New Customer');
        $('#saveCustomer').prop('disabled', false).text('Save');
        $('#customerModal').modal('show');
    };
})();
</script>
@endpush
