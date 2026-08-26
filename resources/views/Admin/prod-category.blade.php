@extends('Dashboard.Layouts.layout')

@push('style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('template/assets/css/select2-custom.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="mb-3">
                <h6 class="ps-2">Product Category</h6>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3" id="formTitle">Add Product Category</h6>
                            <input type="hidden" id="cateId">
                            <div class="mb-3">
                                <label class="form-label">Category Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cateNm" maxlength="50" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Is FMCG<span class="text-danger">*</span></label>
                                <select class="form-select" id="isFmcg">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Agent Commission (%)</label>
                                <input type="number" step="0.01" class="form-control" id="agentComm" max="999.99"
                                    autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purchase Ledger</label>
                                <select class="form-select" id="purLedg">
                                    <option value="0">-- Select Purchase Ledger --</option>
                                    @foreach ($purchaseLedgers as $ledger)
                                        <option value="{{ $ledger->Id }}">{{ $ledger->Ledger_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sale Ledger</label>
                                <select class="form-select" id="saleLedg">
                                    <option value="0">-- Select Sale Ledger --</option>
                                    @foreach ($salesLedgers as $ledger)
                                        <option value="{{ $ledger->Id }}">{{ $ledger->Ledger_Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                                <button class="btn btn-primary" id="saveCategory">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">Product Category List</h6>
                            <div class="table-responsive">
                                <table id="categoryTable" class="table table-nowrap datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Sl</th>
                                            <th>Category Name</th>
                                            <th>FMCG</th>
                                            <th>Agent Comm (%)</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoryTableBody">
                                        @foreach ($categories as $key => $cat)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $cat->Prd_CateNm }}</td>
                                                <td>{{ $cat->Is_Fmcg ? 'Yes' : 'No' }}</td>
                                                <td>{{ $cat->Agent_Comm }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-primary btn-sm editRow"
                                                        data-id="{{ $cat->Prd_CateId }}" data-name="{{ $cat->Prd_CateNm }}"
                                                        data-fmcg="{{ $cat->Is_Fmcg }}" data-comm="{{ $cat->Agent_Comm }}"
                                                        data-purledg="{{ $cat->Pur_Ledg ?? 0 }}"
                                                        data-saledg="{{ $cat->Sale_Ledg ?? 0 }}">Edit</button>
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
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#purLedg').select2({ placeholder: '-- Select Purchase Ledger --', allowClear: true });
            $('#saleLedg').select2({ placeholder: '-- Select Sale Ledger --', allowClear: true });

            $(document).on('click', '.editRow', function() {
                $('#cateId').val($(this).data('id'));
                $('#cateNm').val($(this).data('name'));
                $('#isFmcg').val(String($(this).data('fmcg')));
                $('#agentComm').val($(this).data('comm'));
                $('#purLedg').val($(this).data('purledg')).trigger('change');
                $('#saleLedg').val($(this).data('saledg')).trigger('change');
                $('#formTitle').text('Edit Product Category');
                $('#saveCategory').text('Update');
            });

            $('#cancelBtn').on('click', clearForm);

            $('#saveCategory').click(function() {
                if (!$('#cateNm').val().trim()) {
                    Swal.fire('Validation Error', 'Category Name required', 'error');
                    return;
                }
                $(this).prop('disabled', true).text('Saving...');
                let id = $('#cateId').val();
                $.ajax({
                    url: "{{ route('prod-category.store') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        cate_id: id,
                        cate_nm: $('#cateNm').val().trim(),
                        is_fmcg: $('#isFmcg').val(),
                        agent_comm: $('#agentComm').val(),
                        pur_ledg: $('#purLedg').val() || 0,
                        sale_ledg: $('#saleLedg').val() || 0
                    },
                    success: res => {
                        Swal.fire('Success', res.message, 'success').then(() => location
                        .reload());
                    },
                    error: xhr => {
                        $('#saveCategory').prop('disabled', false).text(id ? 'Update' : 'Save');
                        let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON
                            .errors)[0][0] : (xhr.responseJSON?.error || xhr.responseJSON
                            ?.message || 'Failed to save');
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            function clearForm() {
                $('#cateId').val('');
                $('#cateNm').val('');
                $('#isFmcg').val('0');
                $('#agentComm').val('');
                $('#purLedg').val('0').trigger('change');
                $('#saleLedg').val('0').trigger('change');
                $('#formTitle').text('Add Product Category');
                $('#saveCategory').prop('disabled', false).text('Save');
            }
        });
    </script>
@endpush
