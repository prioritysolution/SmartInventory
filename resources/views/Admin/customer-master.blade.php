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
                <table id="customerTable" class="table table-nowrap datatable">
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
                        @foreach ($customers as $key => $customer)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $customer->Party_Code }}</td>
                            <td>{{ $customer->Party_Name }}</td>
                            <td>{{ $customer->Contact_No }}</td>
                            <td>{{ $customer->Address_Line1 }}</td>
                            <td>{{ $customer->Status_Cd == 1 ? 'Active' : 'Inactive' }}</td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm editRow me-1"
                                    data-id="{{ $customer->Party_Id }}"
                                    data-code="{{ $customer->Party_Code }}"
                                    data-name="{{ $customer->Party_Name }}"
                                    data-mobile="{{ $customer->Contact_No }}"
                                    data-altmobile="{{ $customer->AltContact_No }}"
                                    data-email="{{ $customer->EMail }}"
                                    data-address1="{{ $customer->Address_Line1 }}"
                                    data-address2="{{ $customer->Address_Line2 }}"
                                    data-city="{{ $customer->City }}"
                                    data-district="{{ $customer->District }}"
                                    data-state="{{ $customer->State }}"
                                    data-pin="{{ $customer->PinCode }}"
                                    data-pan="{{ $customer->Pan_No }}"
                                    data-gst="{{ $customer->GstIn }}"
                                    data-statecode="{{ $customer->State_Cd }}"
                                    data-credit="{{ $customer->Credit_Limit }}">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteRow"
                                    data-id="{{ $customer->Party_Id }}"
                                    data-name="{{ $customer->Party_Name }}">
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

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
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
        <label class="form-label">Party Code<span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-lg " id="partyCode" maxlength="25" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Party Name<span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-lg" id="partyName" maxlength="250" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Mobile No<span class="text-danger">*</span></label>
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
        <label class="form-label">Address 1<span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-lg" id="address1" maxlength="200" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Address 2</label>
        <input type="text" class="form-control form-control-lg" id="address2" maxlength="200" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">City</label>
        <input type="text" class="form-control form-control-lg" id="city" maxlength="50" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">District</label>
        <input type="text" class="form-control form-control-lg" id="district" maxlength="50" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">State</label>
        <input type="text" class="form-control form-control-lg" id="state" maxlength="25" autocomplete="off">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Pin Code</label>
        <input type="text" class="form-control form-control-lg" id="pinCode" maxlength="10" autocomplete="off">
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
<script>
$(document).ready(function () {

    $(document).on('click', '.editRow', function () {
        $('#partyId').val($(this).data('id'));
        $('#partyCode').val($(this).data('code')).prop('readonly', true);
        $('#partyName').val($(this).data('name'));
        $('#mobileNo').val($(this).data('mobile'));
        $('#altMobileNo').val($(this).data('altmobile'));
        $('#mailId').val($(this).data('email'));
        $('#address1').val($(this).data('address1'));
        $('#address2').val($(this).data('address2'));
        $('#city').val($(this).data('city'));
        $('#district').val($(this).data('district'));
        $('#state').val($(this).data('state'));
        $('#pinCode').val($(this).data('pin'));
        $('#panNo').val($(this).data('pan'));
        $('#gstin').val($(this).data('gst'));
        $('#stateCode').val($(this).data('statecode'));
        $('#creditLimit').val($(this).data('credit'));
        $('#modalTitle').text('Edit Customer');
        $('#saveCustomer').text('Update');
        $('#customerModal').modal('show');
    });

    $(document).on('click', '.deleteRow', function () {
        let id   = $(this).data('id');
        let name = $(this).data('name');
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
                url: `/customer-master/${id}`,
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                success: res => Swal.fire('Deleted', res.message, 'success').then(() => location.reload()),
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete', 'error')
            });
        });
    });

    $('#saveCustomer').click(function () {
        if (!validateForm()) return;

        $(this).prop('disabled', true).text('Saving...');

        let partyId = $('#partyId').val();
        let url = partyId ? `/customer-master/${partyId}` : "{{ route('customer-master.store') }}";

        let data = {
            _token: "{{ csrf_token() }}",
            party_code:   $('#partyCode').val(),
            party_name:   $('#partyName').val(),
            mobile_no:    $('#mobileNo').val(),
            alt_mobile_no: $('#altMobileNo').val(),
            mail_id:      $('#mailId').val(),
            address1:     $('#address1').val(),
            address2:     $('#address2').val(),
            city:         $('#city').val(),
            district:     $('#district').val(),
            state:        $('#state').val(),
            pin_code:     $('#pinCode').val(),
            pan_no:       $('#panNo').val(),
            gstin:        $('#gstin').val(),
            state_code:   $('#stateCode').val(),
            credit_limit: $('#creditLimit').val()
        };

        if (partyId) data._method = 'PUT';

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function (res) {
                $('#customerModal').modal('hide');
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            },
            error: function (xhr) {
                $('#saveCustomer').prop('disabled', false).text(partyId ? 'Update' : 'Save');
                if (xhr.status === 422) {
                    let message = xhr.responseJSON?.errors
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

function validateForm() {
    if (!$('#partyCode').val()) { Swal.fire('Validation Error', 'Party Code required', 'error'); return false; }
    if (!$('#partyName').val()) { Swal.fire('Validation Error', 'Party Name required', 'error'); return false; }
    if (!$('#mobileNo').val())  { Swal.fire('Validation Error', 'Mobile No required', 'error');  return false; }
    if (!$('#address1').val())  { Swal.fire('Validation Error', 'Address 1 required', 'error');  return false; }
    return true;
}

function openAddModal() {
    $('#partyId').val('');
    $('#partyCode').val('').prop('readonly', false);
    $('#partyName, #mobileNo, #altMobileNo, #mailId').val('');
    $('#address1, #address2, #city, #district, #state, #pinCode').val('');
    $('#panNo, #gstin, #creditLimit').val('');
      $('#stateCode').val('19');
    $('#modalTitle').text('Add New Customer');
    $('#saveCustomer').text('Save');
    $('#customerModal').modal('show');
}
</script>
@endpush
