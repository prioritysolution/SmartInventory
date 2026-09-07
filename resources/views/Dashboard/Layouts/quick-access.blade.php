<style>
    .quick-access-sidebar {
        position: fixed;
        top: 0;
        right: -420px;
        width: 400px;
        max-width: calc(100vw - 24px);
        height: 100%;
        z-index: 1065;
        background: #fff;
        box-shadow: -8px 0 24px rgba(31, 45, 84, 0.12);
        display: flex;
        flex-direction: column;
        transition: right .25s ease;
        padding: 16px;
        box-sizing: border-box;
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
        scrollbar-width: thin;
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
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        width: 100%;
    }

    .quick-access-grid a {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        min-height: 96px;
        padding: 10px 8px;
        text-decoration: none;
        border: 1px solid #e8eaf0;
        border-radius: 8px;
        background: #fff;
        color: #172b4c;
        box-sizing: border-box;
        overflow: hidden;
    }

    .quick-access-grid a:hover {
        border-color: #3F6AD8;
        background: #f5f8ff;
        color: #1e3a8a;
    }

    .quick-access-grid a i {
        font-size: 18px;
        margin-bottom: 8px;
        flex-shrink: 0;
    }

    .quick-access-grid a span {
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        line-height: 1.25;
        width: 100%;
        word-break: break-word;
        overflow-wrap: anywhere;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 575.98px) {
        .quick-access-sidebar {
            width: 100%;
            right: -100%;
            max-width: 100%;
        }

        .quick-access-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
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
            @foreach ($menuLinks ?? [] as $link)
                <a href="{{ route($link->route) }}" title="{{ $link->name }}">
                    <i class="{{ $link->icon }}"></i>
                    <span>{{ $link->name }}</span>
                </a>
            @endforeach
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
