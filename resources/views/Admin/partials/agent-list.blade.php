<div class="d-flex justify-content-end align-items-center mb-3">
    <form method="GET" action="{{ route('agent-profile') }}" id="agentSearchForm" class="d-flex gap-2">
        <input type="text" class="form-control form-control-sm" id="agentSearch" name="search"
            value="{{ request('search', '') }}" placeholder="Search agent..." style="width:250px;">
        <button class="btn btn-primary btn-sm">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        @if (request('search'))
            <button type="button" id="clearAgentSearch" class="btn btn-secondary btn-sm">Clear</button>
        @endif
    </form>
</div>

<div class="table-responsive">
    <table class="table table-nowrap table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Sl</th>
                <th>Agent Code</th>
                <th>Agent Name</th>
                <th>Address</th>
                <th>Mobile No</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody id="agentTableBody">
            @forelse ($agents as $key => $agent)
                    <tr>
                        <td>{{ ($page - 1) * $pageSize + $key + 1 }}</td>
                        <td>{{ $agent->Agent_Code }}</td>
                        <td>{{ $agent->Agent_Name }}</td>
                        <td>{{ $agent->Address }}</td>
                        <td>{{ $agent->Contact_No }}</td>
                        <td class="text-center">
                            <button class="btn btn-primary btn-sm editRow" data-id="{{ $agent->Agent_Id }}"
                                data-code="{{ $agent->Agent_Code }}" data-name="{{ $agent->Agent_Name }}"
                                data-address="{{ $agent->Address }}" data-mobile="{{ $agent->Contact_No }}"
                                data-join="{{ $agent->Join_Date }}" data-limit="{{ $agent->Stock_Limit }}"
                                data-credit-limit="{{ $agent->Credit_Sell_Limit }}"
                                data-village="{{ $agent->Village_Id ?? '' }}" data-ps="{{ $agent->Ps_Id ?? '' }}"
                                data-post="{{ $agent->Post_Id ?? '' }}" data-dist="{{ $agent->Dist_Id ?? '' }}"
                                data-password="">Edit</button>
                        </td>
                    </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">No agents found.</td>
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
                <a class="page-link agent-page-link" href="#" data-page="{{ $page - 1 }}">Previous</a>
            </li>
            @for ($i = 1; $i <= $lastPage; $i++)
                @if ($i == 1 || $i == $lastPage || abs($i - $page) <= 2)
                    <li class="page-item {{ $page == $i ? 'active' : '' }}">
                        <a class="page-link agent-page-link" href="#" data-page="{{ $i }}">{{ $i }}</a>
                    </li>
                @elseif(abs($i - $page) == 3)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endfor
            <li class="page-item {{ $page == $lastPage ? 'disabled' : '' }}">
                <a class="page-link agent-page-link" href="#" data-page="{{ $page + 1 }}">Next</a>
            </li>
        </ul>
    @endif
</div>
