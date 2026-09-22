@extends('Dashboard.Layouts.layout')

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

    <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <h6 class=" ps-2">Supplier Master</h6>
        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
            <a href="javascript:void(0);" class="btn btn-primary d-flex align-items-center" onclick="openAddModal()">
                + Add New
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="supplierTable" class="table table-nowrap datatable">
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
                    <tbody>
                        @foreach ($suppliers as $key => $supplier)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $supplier->Party_Code }}</td>
                            <td>{{ $supplier->Party_Name }}</td>
                            <td>{{ $supplier->Contact_No }}</td>
                            <td>{{ $supplier->Address_Line1 }}</td>
                            <td>{{ $supplier->Status_Cd == 1 ? 'Active' : 'Inactive' }}</td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm editRow me-1"
                                    data-id="{{ $supplier->Party_Id }}"
                                    data-code="{{ $supplier->Party_Code }}"
                                    data-name="{{ $supplier->Party_Name }}"
                                    data-mobile="{{ $supplier->Contact_No }}"
                                    data-altmobile="{{ $supplier->AltContact_No }}"
                                    data-email="{{ $supplier->EMail }}"
                                    data-person="{{ $supplier->Contact_Person }}"
                                    data-designation="{{ $supplier->Designation }}"
                                    data-address1="{{ $supplier->Address_Line1 }}"
                                    data-address2="{{ $supplier->Address_Line2 }}"
                                    data-city="{{ $supplier->City }}"
                                    data-state="{{ $supplier->State }}"
                                    data-pin="{{ $supplier->Pin_Code ?? '' }}"
                                    data-village="{{ $supplier->Village_Id ?? '' }}"
                                    data-ps="{{ $supplier->Ps_Id ?? '' }}"
                                    data-post="{{ $supplier->Post_Id ?? '' }}"
                                    data-dist="{{ $supplier->Dist_Id ?? '' }}"
                                    data-pan="{{ $supplier->Pan_No }}"
                                    data-gst="{{ $supplier->GstIn }}"
                                    data-statecode="{{ $supplier->State_Cd }}"
                                    data-credit="{{ $supplier->Credit_Limit }}"
                                    data-opening="{{ $supplier->Opening_Bal }}">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteRow"
                                    data-id="{{ $supplier->Party_Id }}"
                                    data-name="{{ $supplier->Party_Name }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
<div class="modal-dialog modal-xl" style="max-width: 95%;">
<div class="modal-content"style="min-height: 70vh;">

<div class="modal-header">
    <h5 class="modal-title" id="modalTitle">Add New Supplier</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" id="partyId">

<div class="row g-4">
    <div class="col-md-3 mb-3">
        <label class="form-label">Party Name<span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-lg" id="partyName" maxlength="100" autocomplete="off">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Mobile No</label>
        <input type="text" class="form-control form-control-lg" id="mobileNo" maxlength="10" autocomplete="off"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Whatsapp No</label>
        <input type="text" class="form-control form-control-lg" id="contactNo" maxlength="10" autocomplete="off"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Mail Id</label>
        <input type="email" class="form-control form-control-lg" id="mailId" maxlength="50" autocomplete="off">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Contact Person</label>
        <input type="text" class="form-control form-control-lg" id="contactPerson" maxlength="150" autocomplete="off">
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Designation</label>
        <input type="text" class="form-control form-control-lg" id="designation" maxlength="50" autocomplete="off">
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
    <button class="btn btn-primary" id="saveSupplier">Save</button>
</div>

</div>
</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('template/assets/js/address-master-form.js') }}?v=2"></script>
<script>
$(document).ready(function () {

    $(document).on('click', '.editRow', function () {
        $('#partyId').val($(this).data('id'));
        $('#partyName').val($(this).data('name'));
        $('#mobileNo').val($(this).data('mobile'));
        $('#contactNo').val($(this).data('altmobile'));
        $('#mailId').val($(this).data('email'));
        $('#contactPerson').val($(this).data('person'));
        $('#designation').val($(this).data('designation'));
        $('#address1').val($(this).data('address1'));
        $('#address2').val($(this).data('address2'));
        $('#city').val($(this).data('city'));
        $('#state').val($(this).data('state'));
        AddressMasterForm.setValues('', {
            village_id: $(this).data('village'),
            ps_id: $(this).data('ps'),
            post_id: $(this).data('post'),
            dist_id: $(this).data('dist'),
            pin_code: $(this).data('pin')
        });
        $('#panNo').val($(this).data('pan'));
        $('#gstin').val($(this).data('gst'));
        $('#stateCode').val($(this).data('statecode'));
        $('#creditLimit').val($(this).data('credit'));
        $('#openingBalance').val($(this).data('opening'));
        $('#modalTitle').text('Edit Supplier');
        $('#saveSupplier').text('Update');
        $('#supplierModal').modal('show');
    });

    $(document).on('click', '.deleteRow', function () {
        let id   = $(this).data('id');
        let name = $(this).data('name');
        Swal.fire({
            title: 'Delete Supplier?',
            text: name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/supplier-master/${id}`,
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                success: res => Swal.fire('Deleted', res.message, 'success').then(() => location.reload()),
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete', 'error')
            });
        });
    });

    $('#saveSupplier').click(function () {
        if (!validateForm()) return;

        $(this).prop('disabled', true).text('Saving...');

        let partyId = $('#partyId').val();
        let url = partyId ? `/supplier-master/${partyId}` : "{{ route('supplier-master.store') }}";

        let data = Object.assign({
            _token: "{{ csrf_token() }}",
            party_name: $('#partyName').val(),
            mobile_no: $('#mobileNo').val(),
            contact_no: $('#contactNo').val(),
            mail_id: $('#mailId').val(),
            contact_person: $('#contactPerson').val(),
            designation: $('#designation').val(),
            address1: $('#address1').val(),
            address2: $('#address2').val(),
            city: $('#city').val(),
            district: AddressMasterForm.selectedName('', 'dist'),
            state: $('#state').val(),
            pin_code: AddressMasterForm.selectedName('', 'pin'),
            pan_no: $('#panNo').val(),
            gstin: $('#gstin').val(),
            state_code: $('#stateCode').val(),
            credit_limit: $('#creditLimit').val(),
            opening_balance: $('#openingBalance').val()
        }, AddressMasterForm.collect(''));

        if (partyId) data._method = 'PUT';

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function (res) {
                $('#supplierModal').modal('hide');
                let msg = res.message + (res.party_code ? '<br><b>Party Code: ' + res.party_code + '</b>' : '');
                Swal.fire({ title: 'Success', html: msg, icon: 'success' }).then(() => location.reload());
            },
            error: function (xhr) {
                $('#saveSupplier').prop('disabled', false).text(partyId ? 'Update' : 'Save');
                if (xhr.status === 422) {
                    let message = xhr.responseJSON?.errors
                        ? Object.values(xhr.responseJSON.errors)[0][0]
                        : (xhr.responseJSON?.error || 'Validation failed');
                    Swal.fire('Validation Error', message, 'error');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save supplier', 'error');
                }
            }
        });
    });
});

function validateForm() {
    if (!$('#partyName').val()) { Swal.fire('Validation Error', 'Party Name required', 'error'); return false; }
    if ($('#mobileNo').val() && !/^\d{10}$/.test($('#mobileNo').val())) { Swal.fire('Validation Error', 'Mobile No must be 10 digits', 'error'); return false; }
    return true;
}

function openAddModal() {
    $('#partyId').val('');
    $('#partyName, #mobileNo, #contactNo, #mailId, #contactPerson, #designation').val('');
    $('#address1, #address2, #city, #state').val('');
    AddressMasterForm.clearValues('');
    $('#panNo, #gstin, #creditLimit, #openingBalance').val('');
      $('#stateCode').val('19');
    $('#modalTitle').text('Add New Supplier');
    $('#saveSupplier').text('Save');
    $('#supplierModal').modal('show');
}
</script>
@endpush
