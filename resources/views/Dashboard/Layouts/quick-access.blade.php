<style>
    .quick-access-sidebar {
        position: fixed;
        top: 0;
        right: -320px;
        width: 320px;
        height: 100%;
        z-index: 1065;
        background: #fff;
        box-shadow: -8px 0 24px rgba(31, 45, 84, 0.12);
        display: flex;
        flex-direction: column;
        transition: right .25s ease;
        padding: 16px 12px 12px;
    }

    .quick-access-sidebar.is-open {
        right: 0;
    }

    .quick-access-bg {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.35);
        z-index: 1060;
        display: none;
    }

    .quick-access-bg.is-open {
        display: block;
    }

    .quick-access-panel {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .quick-access-panel::-webkit-scrollbar {
        display: none;
    }

    .quick-access-panel .qa-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        flex-shrink: 0;
    }

    .quick-access-panel .qa-head h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #172b4c;
    }

    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        width: 100%;
    }

    .quick-access-grid a {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 1 / 1;
        padding: 6px 4px;
        text-decoration: none;
        border: 1px solid #e8eaf0;
        border-radius: 8px;
        background: #fff;
        color: #172b4c;
    }

    .quick-access-grid a:hover {
        border-color: #3F6AD8;
        background: #f5f8ff;
        color: #1e3a8a;
    }

    .quick-access-grid a i {
        font-size: 18px;
        margin-bottom: 6px;
    }

    .quick-access-grid a span {
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        line-height: 1.2;
    }
</style>

<aside class="quick-access-sidebar" id="quickAccessSidebar">
    <div class="quick-access-panel">
        <div class="qa-head">
            <h4>Quick Access</h4>
            <button type="button" id="quickAccessClose" aria-label="Close"
                style="width:30px;height:30px;border-radius:50%;border:none;background:#dc3545;color:#fff;display:flex;align-items:center;justify-content:center;padding:0;flex-shrink:0;">
                <i class="fa-solid fa-xmark" style="font-size:14px;"></i>
            </button>
        </div>
        <div class="quick-access-grid">
            <a href="{{ route('user-dashboard') }}"><i class="fa fa-home text-primary"></i><span>Dashboard</span></a>
            <a href="{{ route('counter-sale') }}"><i class="fa fa-shopping-cart text-warning"></i><span>Counter Sale</span></a>
            <a href="{{ route('good-received-entry') }}"><i class="fa fa-truck text-success"></i><span>Goods Received</span></a>
            <a href="{{ route('agent-indent') }}"><i class="fa fa-clipboard-list text-info"></i><span>Agent Indent</span></a>
            <a href="{{ route('sale-return') }}"><i class="fa fa-rotate-left text-danger"></i><span>Counter Sale Return</span></a>
            <a href="{{ route('purchase-return') }}"><i class="fa fa-undo text-warning"></i><span>Purchase Return</span></a>
            <a href="{{ route('agent-return') }}"><i class="fa fa-repeat text-secondary"></i><span>Agent Return</span></a>
            <a href="{{ route('barcode-label') }}"><i class="fa fa-tags text-primary"></i><span>Barcode Label</span></a>
            <a href="{{ route('print-barcode') }}"><i class="fa fa-barcode text-success"></i><span>Print Barcode</span></a>
            <a href="{{ route('product-master') }}"><i class="fa fa-box text-info"></i><span>Products</span></a>
            <a href="{{ route('supplier-master') }}"><i class="fa fa-warehouse text-warning"></i><span>Suppliers</span></a>
            <a href="{{ route('customer-master') }}"><i class="fa fa-users text-danger"></i><span>Customers</span></a>
            <a href="{{ route('agent-profile') }}"><i class="fa fa-user-tie text-primary"></i><span>Agents</span></a>
            <a href="{{ route('member-share') }}"><i class="fa fa-handshake text-success"></i><span>Members</span></a>
            <a href="{{ route('gst-codes') }}"><i class="fa fa-receipt text-warning"></i><span>GST Codes</span></a>
            <a href="{{ route('chart-of-accounts') }}"><i class="fa fa-sitemap text-info"></i><span>Accounts</span></a>
            <a href="{{ route('prod-category') }}"><i class="fa fa-layer-group text-primary"></i><span>Category</span></a>
            <a href="{{ route('user-creation') }}"><i class="fa fa-user-plus text-danger"></i><span>Users</span></a>
        </div>
    </div>
</aside>
<div class="quick-access-bg" id="quickAccessBg"></div>

@push('scripts')
    <script>
        (function() {
            const sidebar = document.getElementById('quickAccessSidebar');
            const overlay = document.getElementById('quickAccessBg');
            const openBtns = document.querySelectorAll('[data-toggle="quick-access"]');
            const closeBtn = document.getElementById('quickAccessClose');

            function openPanel() {
                sidebar.classList.add('is-open');
                overlay.classList.add('is-open');
            }

            function closePanel() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
            }

            openBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar.classList.contains('is-open')) {
                        closePanel();
                    } else {
                        openPanel();
                    }
                });
            });

            if (closeBtn) closeBtn.addEventListener('click', closePanel);
            if (overlay) overlay.addEventListener('click', closePanel);
        })();
    </script>
@endpush
