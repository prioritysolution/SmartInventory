@extends('Dashboard.Layouts.layout')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <h6 class="ps-2">GST Codes</h6>
                <a href="javascript:void(0);" class="btn btn-primary" onclick="openAddModal()">+ Add New</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="gstTable" class="table table-nowrap datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Sl</th>
                                    <th>HSN</th>
                                    <th>Description</th>
                                    <th>Tax%</th>
                                    <th>SGST</th>
                                    <th>CGST</th>
                                    <th>IGST</th>
                                    <th>UGST</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gstCodes as $key => $g)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $g->Gst_Code }}</td>
                                        <td>{{ $g->Category }}</td>
                                        <td>{{ $g->Tax_Percent }}</td>
                                        <td>{{ $g->Sgst }}</td>
                                        <td>{{ $g->Cgst }}</td>
                                        <td>{{ $g->Igst }}</td>
                                        <td>{{ $g->Ugst }}</td>
                                       <td>{!! $g->Is_Active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm editGst" data-id="{{ $g->Id }}"
                                                data-code="{{ $g->Gst_Code }}" data-category="{{ $g->Category }}"
                                                data-tax="{{ $g->Tax_Percent }}" data-sgst="{{ $g->Sgst }}"
                                                data-cgst="{{ $g->Cgst }}" data-igst="{{ $g->Igst }}"
                                                data-ugst="{{ $g->Ugst }}"
                                                data-active="{{ $g->Is_Active }}">Edit</button>
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

    <div class="modal fade" id="gstModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gstModalTitle">Add GST Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="gstId">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">HSN<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gstCode" maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Description<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gstCategory" maxlength="50" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax Percent (%)<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="taxPercent" max="100"
                                autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SGST (%)<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="sgst" max="100"
                                autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CGST (%)<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="cgst" max="100"
                                autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">IGST (%)<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="igst" max="100"
                                autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">UGST (%)</label>
                            <input type="number" step="0.01" class="form-control" id="ugst" max="100"
                                autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status<span class="text-danger">*</span></label>
                            <select class="form-select" id="gstActive">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveGst">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.editGst', function() {
                $('#gstId').val($(this).data('id'));
                $('#gstCode').val($(this).data('code'));
                $('#gstCategory').val($(this).data('category'));
                $('#taxPercent').val($(this).data('tax'));
                $('#sgst').val($(this).data('sgst'));
                $('#cgst').val($(this).data('cgst'));
                $('#igst').val($(this).data('igst'));
                $('#ugst').val($(this).data('ugst'));
                $('#gstActive').val(String($(this).data('active')));
                $('#gstModalTitle').text('Edit GST Code');
                $('#saveGst').text('Update');
                $('#gstModal').modal('show');
            });

            $('#saveGst').click(function() {
                if (!$('#gstCode').val().trim()) {
                    Swal.fire('Validation Error', 'GST Code required', 'error');
                    return;
                }
                if (!$('#gstCategory').val().trim()) {
                    Swal.fire('Validation Error', 'Category required', 'error');
                    return;
                }
                if ($('#taxPercent').val() === '') {
                    Swal.fire('Validation Error', 'Tax Percent required', 'error');
                    return;
                }
                if ($('#sgst').val() === '') {
                    Swal.fire('Validation Error', 'SGST required', 'error');
                    return;
                }
                if ($('#cgst').val() === '') {
                    Swal.fire('Validation Error', 'CGST required', 'error');
                    return;
                }
                if ($('#igst').val() === '') {
                    Swal.fire('Validation Error', 'IGST required', 'error');
                    return;
                }

                $(this).prop('disabled', true).text('Saving...');
                let id = $('#gstId').val();
                let url = id ? `/gst-codes/${id}` : "{{ route('gst-codes.store') }}";
                let data = {
                    _token: "{{ csrf_token() }}",
                    gst_code: $('#gstCode').val().trim(),
                    category: $('#gstCategory').val().trim(),
                    tax_percent: $('#taxPercent').val(),
                    sgst: $('#sgst').val(),
                    cgst: $('#cgst').val(),
                    igst: $('#igst').val(),
                    ugst: $('#ugst').val(),
                    is_active: $('#gstActive').val()
                };
                if (id) data._method = 'PUT';

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: res => {
                        $('#gstModal').modal('hide');
                        Swal.fire('Success', res.message, 'success').then(() => location
                        .reload());
                    },
                    error: xhr => {
                        $('#saveGst').prop('disabled', false).text(id ? 'Update' : 'Save');
                        let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON
                            .errors)[0][0] : (xhr.responseJSON?.error || xhr.responseJSON
                            ?.message || 'Failed to save');
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            $('#gstModal').on('hidden.bs.modal', function() {
                $('#gstId, #gstCode, #gstCategory, #taxPercent, #sgst, #cgst, #igst, #ugst').val('');
                $('#gstActive').val('1');
                $('#gstModalTitle').text('Add GST Code');
                $('#saveGst').prop('disabled', false).text('Save');
            });
        });

        function openAddModal() {
            $('#gstModal').modal('show');
        }
    </script>
@endpush
