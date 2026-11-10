<div class="d-flex justify-content-end align-items-center mb-3">
    <form method="GET" action="{{ route('supplier-master') }}" id="supplierSearchForm" class="d-flex gap-2">
        <input type="text" class="form-control form-control-sm" id="supplierSearch" name="search"
            value="{{ $search }}" placeholder="Search supplier..." style="width:250px;">
        <button class="btn btn-primary btn-sm">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        @if ($search !== '')
            <button type="button" id="clearSupplierSearch" class="btn btn-secondary btn-sm">Clear</button>
        @endif
    </form>
</div>

<div class="table-responsive">
    <table class="table table-nowrap table-bordered">
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
            @forelse ($suppliers as $key => $supplier)
                <tr>
                    <td>{{ ($page - 1) * $pageSize + $key + 1 }}</td>
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
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No suppliers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
        Showing {{ $total > 0 ? ($page - 1) * $pageSize + 1 : 0 }} to {{ min($page * $pageSize, $total) }} of
        {{ $total }} entries
    </small>
    @if ($lastPage > 1)
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                <a class="page-link supplier-page-link" href="#" data-page="{{ $page - 1 }}">Previous</a>
            </li>
            @for ($i = 1; $i <= $lastPage; $i++)
                @if ($i == 1 || $i == $lastPage || abs($i - $page) <= 2)
                    <li class="page-item {{ $page == $i ? 'active' : '' }}">
                        <a class="page-link supplier-page-link" href="#" data-page="{{ $i }}">{{ $i }}</a>
                    </li>
                @elseif(abs($i - $page) == 3)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endfor
            <li class="page-item {{ $page == $lastPage ? 'disabled' : '' }}">
                <a class="page-link supplier-page-link" href="#" data-page="{{ $page + 1 }}">Next</a>
            </li>
        </ul>
    @endif
</div>
