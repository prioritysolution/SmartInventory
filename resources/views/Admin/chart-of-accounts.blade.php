@extends('Dashboard.Layouts.layout')
<div id="pageLoader"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:9999; justify-content:center; align-items:center;">
    <div class="spinner-border text-primary" style="width:3rem; height:3rem;"></div>
</div>

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="mb-3">
                <h6 class="ps-2">Chart of Accounts</h6>
            </div>

            <div class="row">
                <!-- Left: 4 boxes -->
                <div class="col-md-3">
                    <div class="d-flex flex-column gap-3">
                        <div class="card chart-box cursor-pointer border-2" id="box-category"
                            onclick="loadSection('category')">
                        <div class="card-body d-flex align-items-center gap-3" style="min-height: 100px;">

                                <div class="avatar avatar-md bg-primary-subtle rounded">
                                    <i class="fa-solid fa-layer-group text-primary fs-18"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Account Category</h6>
                                    <small class="text-muted">View categories</small>
                                </div>
                            </div>
                        </div>
                        <div class="card chart-box cursor-pointer border-2" id="box-mainhd" onclick="loadSection('mainhd')">
                         <div class="card-body d-flex align-items-center gap-3" style="min-height: 100px;">

                                <div class="avatar avatar-md bg-success-subtle rounded">
                                    <i class="fa-solid fa-sitemap text-success fs-18"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Main Head</h6>
                                    <small class="text-muted">View main heads</small>
                                </div>
                            </div>
                        </div>
                        <div class="card chart-box cursor-pointer border-2" id="box-glhead" onclick="loadSection('glhead')">
                          <div class="card-body d-flex align-items-center gap-3" style="min-height: 100px;">

                                <div class="avatar avatar-md bg-warning-subtle rounded">
                                    <i class="fa-solid fa-book text-warning fs-18"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">GL Head</h6>
                                    <small class="text-muted">View GL heads</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Data panel -->
                <div class="col-md-9">
                    <div class="card" id="dataPanel" style="display:none;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0" id="panelTitle"></h6>
                            </div>
                            <div class="table-responsive">
                                <div id="loadingSpinner" class="text-center py-3" style="display:none;">
                                    <div class="spinner-border spinner-border-sm text-primary"></div> Loading...
                                </div>
                                <table id="chartTable" class="table table-nowrap" style="display:none;">
                                    <thead class="thead-light" id="tableHead"></thead>
                                    <tbody id="tableBody"></tbody>
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
    <style>
        .chart-box {
            cursor: pointer;
            transition: all 0.2s;
        }

        .chart-box:hover,
        .chart-box.active {
            border-color: #3F6AD8 !important;
            box-shadow: 0 0 0 2px rgba(63, 106, 216, 0.15);
        }
    </style>
    <script>
        let currentSection = '';
        let chartDT = null;

        function destroyChartTable() {
            if (chartDT) {
                chartDT.destroy();
                chartDT = null;
            } else if ($.fn.DataTable.isDataTable('#chartTable')) {
                $('#chartTable').DataTable().destroy();
            }
        }

        function loadSection(type) {
            $('.chart-box').removeClass('active');
            $('#box-' + type).addClass('active');

            currentSection = type;
            $('#dataPanel').show();
            $('#pageLoader').css('display', 'flex');
            destroyChartTable();
            $('#chartTable').hide();

            const titles = {
                category: 'Account Category',
                mainhd: 'Main Head',
                glhead: 'GL Head'
            };
            $('#panelTitle').text(titles[type]);
            $('#tableHead').html('');
            $('#tableBody').html('');

            $.get("{{ url('chart-of-accounts/data') }}/" + type, function(data) {
                let head = '',
                    body = '';

                if (type === 'category') {
                    head = '<tr><th>Sl</th><th>Code</th><th>Description</th></tr>';
                    data.forEach((r, i) => {
                        body += `<tr><td>${i+1}</td><td>${r.Cate_Code ?? ''}</td><td>${r.Cate_Desc ?? ''}</td></tr>`;
                    });
                } else if (type === 'mainhd') {
                    head = '<tr><th>Sl</th><th>Code</th><th>Description</th><th>Category</th></tr>';
                    data.forEach((r, i) => {
                        body +=
                            `<tr><td>${i+1}</td><td>${r.MainHd_Code ?? ''}</td><td>${r.MainHd_Desc ?? ''}</td><td>${r.Cate_Desc ?? ''}</td></tr>`;
                    });
                } else if (type === 'glhead') {
                    head =
                        '<tr><th>Sl</th><th>Code</th><th>Description</th><th>Account For</th><th>Main Head</th><th>Status</th></tr>';
                    data.forEach((r, i) => {
                        body +=
                            `<tr><td>${i+1}</td><td>${r.Account_Code ?? ''}</td><td>${r.Account_Desc ?? ''}</td><td>${r.Account_For ?? ''}</td><td>${r.MainHd_Desc ?? ''}</td><td>${r.Is_Active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td></tr>`;
                    });
                }

                $('#tableHead').html(head);
                $('#tableBody').html(body);
                $('#pageLoader').hide();
                $('#chartTable').show();
                chartDT = $('#chartTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    autoWidth: false,
                    sDom: 'fBtlpi',
                    language: {
                        search: '',
                        searchPlaceholder: 'Search...',
                        sLengthMenu: 'Row Per Page _MENU_ Entries',
                        info: '_START_ - _END_ of _TOTAL_ items',
                        emptyTable: 'No data found',
                        paginate: {
                            next: '<i class="isax isax-arrow-right-1"></i>',
                            previous: '<i class="isax isax-arrow-left"></i>'
                        }
                    }
                });
            }).fail(function() {
                $('#pageLoader').hide();
                $('#chartTable').show();
            });
        }
    </script>
@endpush
