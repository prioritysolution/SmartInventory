@extends('Dashboard.Layouts.layout')

@push('style')
<style>
.sp-card { background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:16px; }
.sp-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; }
.sp-badge.OPEN { background:#e8f5e9; color:#2e7d32; }
.sp-badge.IN_PROGRESS { background:#fff3e0; color:#e65100; }
.sp-badge.CLOSED { background:#f3f4f6; color:#6b7280; }
.sp-pri.URGENT { color:#c62828; font-weight:700; }
.sp-pri.HIGH { color:#e65100; font-weight:600; }
.sp-pri.MEDIUM { color:#1565c0; }
.sp-pri.LOW { color:#558b2f; }
.sp-thread { max-height:420px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; padding:4px 0; }
.sp-bubble { max-width:75%; padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5; }
.sp-bubble.client { background:#e3f2fd; align-self:flex-end; border-bottom-right-radius:3px; }
.sp-bubble.staff { background:#f1f3f5; align-self:flex-start; border-bottom-left-radius:3px; }
.sp-bubble .sp-meta { font-size:11px; color:#888; margin-top:4px; }
.sp-bubble img { max-width:180px; border-radius:6px; margin-top:6px; cursor:pointer; }
.sp-view { display:none; }
.sp-view.active { display:block; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
<div class="content container-fluid">

<div class="d-flex justify-content-between align-items-center ps-2 mb-3">
    <h6 class="mb-0">Support <span class="text-muted fw-normal" id="spProjectName"></span></h6>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-primary btn-sm" id="spNewTicketBtn">
            <i class="fa-solid fa-plus me-1"></i> New Ticket
        </button>
    </div>
</div>

{{-- VIEWS --}}
<div id="spListView" class="sp-view active">
    {{-- Filters --}}
    <div class="sp-card mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" class="form-control form-control-sm" id="spSearch" placeholder="Ticket no / subject">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select class="form-select form-select-sm" id="spFilterStatus">
                    <option value="">All</option>
                    <option value="OPEN">Open</option>
                    <option value="IN_PROGRESS">In Progress</option>
                    <option value="CLOSED">Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Priority</label>
                <select class="form-select form-select-sm" id="spFilterPriority">
                    <option value="">All</option>
                    <option value="URGENT">Urgent</option>
                    <option value="HIGH">High</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="LOW">Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">From</label>
                <input type="date" class="form-control form-control-sm" id="spFromDate">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">To</label>
                <input type="date" class="form-control form-control-sm" id="spToDate">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm w-100" id="spSearchBtn">Go</button>
                <button class="btn btn-secondary btn-sm w-100" id="spResetBtn">Reset</button>
            </div>
        </div>
    </div>

    <div class="sp-card">
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover mb-2">
                <thead class="thead-light">
                    <tr>
                        <th>Sl</th>
                        <th>Ticket No</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Note</th>
                        <th>Raised On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="spTicketBody">
                    <tr><td colspan="8" class="text-center text-muted py-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted" id="spPagInfo"></small>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="spPrevBtn">Previous</button>
                <button class="btn btn-outline-secondary btn-sm" id="spNextBtn">Next</button>
            </div>
        </div>
    </div>
</div>

{{-- TICKET DETAIL VIEW --}}
<div id="spDetailView" class="sp-view">
    <button class="btn btn-outline-secondary btn-sm mb-3" id="spBackBtn">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </button>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="sp-card h-100">
                <h6 class="mb-3">Ticket Info</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted fw-normal" style="width:40%">Number</th><td id="dtNumber" class="fw-semibold"></td></tr>
                    <tr><th class="text-muted fw-normal">Subject</th><td id="dtSubject"></td></tr>
                    <tr><th class="text-muted fw-normal">Status</th><td id="dtStatus"></td></tr>
                    <tr><th class="text-muted fw-normal">Priority</th><td id="dtPriority"></td></tr>
                    <tr><th class="text-muted fw-normal">Raised On</th><td id="dtCreated"></td></tr>
                    <tr><th class="text-muted fw-normal">Note</th><td id="dtRemark" class="text-muted fst-italic"></td></tr>
                </table>
                <div id="dtScreenshots" class="mt-2 d-flex flex-wrap gap-2"></div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="sp-card d-flex flex-column" style="height:520px;">
                <h6 class="mb-3">Messages</h6>
                <div class="sp-thread flex-grow-1" id="spThread"></div>
                <div id="spClosedNote" class="text-center text-muted small py-2" style="display:none;">
                    This ticket is closed. No new replies can be sent.
                </div>
                <div id="spReplyBox" class="mt-3 border-top pt-3">
                    <textarea class="form-control form-control-sm mb-2" id="spReplyText" rows="2" placeholder="Type your reply..."></textarea>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="btn btn-outline-secondary btn-sm mb-0">
                                <i class="fa-solid fa-paperclip"></i> Attach
                                <input type="file" id="spReplyFiles" accept="image/png,image/jpeg,image/webp,image/gif" multiple style="display:none;">
                            </label>
                            <small class="text-muted ms-2" id="spFileCount"></small>
                        </div>
                        <button class="btn btn-primary btn-sm" id="spSendReply">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- NEW TICKET MODAL --}}
<div class="modal fade" id="spNewTicketModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Raise New Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ntSubject" maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" id="ntPriority">
                            <option value="MEDIUM">Medium</option>
                            <option value="LOW">Low</option>
                            <option value="HIGH">High</option>
                            <option value="URGENT">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Attach Images</label>
                        <input type="file" class="form-control" id="ntFiles" accept="image/png,image/jpeg,image/webp,image/gif" multiple>
                        <small class="text-muted">PNG, JPG, WEBP, or GIF. Up to 10, max 20 MB each</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="ntDesc" rows="4" maxlength="4000" placeholder="Describe your issue..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="ntSubmitBtn">Submit Ticket</button>
            </div>
        </div>
    </div>
</div>

{{-- Image Lightbox --}}
<div class="modal fade" id="spImgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <img id="spImgFull" src="" style="max-width:100%; border-radius:8px;">
            </div>
        </div>
    </div>
</div>

</div>
</div>
@endsection

@push('scripts')
<script>
const SP_API       = @json($spApi);
const SP_BASE      = @json($spBase);
const SP_API_KEY   = @json($spApiKey);
const SP_DOMAIN    = window.location.host;

// ── Identity from server session (never exposed as editable form) ──
const SP_IDENTITY = {
    name:  @json($spName),
    phone: @json($spPhone),
    email: @json($spEmail),
};

// ── State ──
let spPhone = '', spName = '', spEmail = '', spToken = '';
let spPage = 1, spPageSize = 20, spTotal = 0, spFrom = 0, spTo = 0;
let spSearch = '', spStatus = '', spPriority = '', spFromDate = '', spToDate = '';
let spOpenNumber = '', spOpenTicketId = '';
let spEcho = null, spCustomerChannel = null, spTicketChannel = null, spTicketChannelName = '';
let spReplyFiles = [];

// ── Helpers ──
function spHeaders(extra, withToken) {
    const headers = Object.assign({ 'Accept': 'application/json', 'X-Api-Key': SP_API_KEY }, extra || {});
    if (withToken !== false) {
        const token = window.SupportLive ? SupportLive.token() : spToken;
        if (token) headers['X-Client-Token'] = token;
    }
    return headers;
}

async function supportFetch(path, options) {
    options = options || {};
    const response = await fetch(SP_API + path, {
        method: options.method || 'GET',
        credentials: 'omit',
        headers: spHeaders(options.body ? { 'Content-Type': 'application/json' } : {}, options.withToken),
        body: options.body ? JSON.stringify(options.body) : undefined
    });
    const body = await response.json().catch(function () { return null; });
    if (!response.ok || !body || body.success === false) {
        const error = new Error((body && body.message) || 'The request could not be completed.');
        error.status = response.status;
        error.errors = body && body.errors ? body.errors : null;
        throw error;
    }
    return body;
}

function spFmt(dt) {
    if (!dt) return '';
    return dt.replace('T', ' ').substring(0, 16);
}

async function spLoadProject() {
    try {
        const json = await supportFetch('/integrations/redirect/project?domain=' + encodeURIComponent(SP_DOMAIN), { withToken: false });
        if (json.data?.domain !== SP_DOMAIN) {
            throw new Error('Unable to load the support project');
        }
        return json.data;
    } catch (error) {
        Swal.fire('Support unavailable', error.message || 'Unable to load the support project', 'error');
        return null;
    }
}

function spShowView(name) {
    document.querySelectorAll('.sp-view').forEach(el => el.classList.remove('active'));
    document.getElementById('sp' + name + 'View').classList.add('active');
}

function spImgUrl(url) {
    if (!url) return '';
    if (url.startsWith('http')) return url;
    return SP_BASE + url;
}

// ── File → base64 ──
function fileToBase64(file) {
    return new Promise((res, rej) => {
        const r = new FileReader();
        r.onload = e => res(e.target.result.split(',')[1]);
        r.onerror = rej;
        r.readAsDataURL(file);
    });
}

async function filesToScreenshots(files) {
    const out = [];
    const list = Array.from(files).slice(0, 10);
    if (files.length > 10) { Swal.fire('Error', 'Attach up to 10 screenshots.', 'error'); return null; }
    for (const f of list) {
        if (!/^image\/(png|jpeg|webp|gif)$/.test(f.type)) { Swal.fire('Error', 'Each screenshot must be an image.', 'error'); return null; }
        if (f.size > 20 * 1024 * 1024) { Swal.fire('Error', 'Each screenshot must be 20 MB or smaller.', 'error'); return null; }
        out.push({ name: f.name, data: await fileToBase64(f) });
    }
    return out;
}

// ── Identity ──
async function spCheckIdentity() {
    spName  = SP_IDENTITY.name;
    spPhone = SP_IDENTITY.phone;
    spEmail = SP_IDENTITY.email;
    if (!spPhone) {
        Swal.fire('Support unavailable', 'Your account has no phone number. Add one before using support.', 'error');
        return;
    }
    const project = await spLoadProject();
    if (!project) return;
    $('#spProjectName').text('· ' + (project.name || ''));
    if (window.SupportLive && SupportLive.phone && SupportLive.phone !== spPhone) {
        Swal.fire('Support unavailable', 'Support is using a different signed-in phone.', 'error');
        return;
    }
    if (window.SupportLive && SupportLive.token()) {
        spToken = SupportLive.token();
        await spInitPage();
        const open = new URLSearchParams(window.location.search).get('ticket');
        if (open) spOpenTicket(open);
    } else {
        spRenderTickets([], 0, 0, 0);
    }
}

async function spInitPage() {
    await spLoadTickets();
}

// ── Ticket List ──
async function spLoadTickets() {
    spToken = window.SupportLive ? SupportLive.token() : spToken;
    if (!spPhone || !spToken) return;
    const params = new URLSearchParams({ phone: spPhone, page: spPage, pageSize: spPageSize });
    if (spSearch) params.set('search', spSearch);
    if (spPriority) params.set('priority', spPriority);
    if (spStatus) params.set('status', spStatus);
    if (spFromDate) params.set('fromDate', spFromDate);
    if (spToDate) params.set('toDate', spToDate);
    try {
        const json = await supportFetch('/integrations/redirect/tickets?' + params.toString());
        const d = json.data;
        spTotal = d.total; spFrom = d.from; spTo = d.to;
        spRenderTickets(d.items || [], d.total, d.from, d.to);
    } catch (e) {
        $('#spTicketBody').html('<tr><td colspan="8" class="text-center text-danger">' + (e.message || 'Failed to load') + '</td></tr>');
    }
}

function spRenderTickets(items, total, from, to) {
    if (!items.length) {
        $('#spTicketBody').html('<tr><td colspan="8" class="text-center text-muted py-3">No tickets found</td></tr>');
        $('#spPagInfo').text('');
        $('#spPrevBtn, #spNextBtn').prop('disabled', true);
        return;
    }
    const rank = { OPEN: 0, IN_PROGRESS: 1, CLOSED: 2, URGENT: 0, HIGH: 1, MEDIUM: 2, LOW: 3 };
    items = items.slice().sort((a, b) => {
        const status = (rank[a.status] ?? 9) - (rank[b.status] ?? 9);
        if (status) return status;
        const priority = (rank[a.priority] ?? 9) - (rank[b.priority] ?? 9);
        if (priority) return priority;
        return String(a.createdAt || '').localeCompare(String(b.createdAt || ''));
    });
    let html = '';
    items.forEach((t, i) => {
        const fresh = window.SupportLive && SupportLive.unreadHas(t.number);
        html += `<tr class="${fresh ? 'support-row-updated' : ''}">
            <td>${from + i}</td>
            <td><a href="#" class="spOpenTicket fw-semibold" data-number="${t.number}">${t.number}</a>${fresh ? ' <span class="badge bg-warning text-dark">New reply</span>' : ''}</td>
            <td>${t.subject}</td>
            <td><span class="sp-pri ${t.priority}">${t.priority}</span></td>
            <td><span class="sp-badge ${t.status}">${t.status.replace('_',' ')}</span></td>
            <td class="text-muted fst-italic small">${t.remark || ''}</td>
            <td>${spFmt(t.createdAt)}</td>
            <td><button class="btn btn-sm btn-outline-primary spOpenTicket" data-number="${t.number}">View</button></td>
        </tr>`;
    });
    $('#spTicketBody').html(html);
    $('#spPagInfo').text('Showing ' + from + ' to ' + to + ' of ' + total);
    $('#spPrevBtn').prop('disabled', spPage <= 1);
    $('#spNextBtn').prop('disabled', to >= total);
}

// ── Open Ticket ──
async function spOpenTicket(number) {
    spOpenNumber = number;
    if (window.SupportLive) {
        SupportLive.clearTicket(number);
        SupportLive.setOpenNumber(number);
    }
    spShowView('Detail');
    await spLoadTicketDetail(number);
    await spLoadMessages(number);
}
window.spOpenTicket = spOpenTicket;

async function spLoadTicketDetail(number) {
    try {
        const json = await supportFetch(`${'/integrations/redirect/tickets/' + encodeURIComponent(number)}?phone=${encodeURIComponent(spPhone)}`);
        const t = json.data;
        spOpenTicketId = t.id || '';
        $('#dtNumber').text(t.number);
        $('#dtSubject').text(t.subject);
        $('#dtStatus').html(`<span class="sp-badge ${t.status}">${t.status.replace('_',' ')}</span>`);
        $('#dtPriority').html(`<span class="sp-pri ${t.priority}">${t.priority}</span>`);
        $('#dtCreated').text(spFmt(t.createdAt));
        $('#dtRemark').text(t.remark || '—');
        const shots = t.screenshots || [];
        $('#dtScreenshots').html(shots.map(s => `<img src="${spImgUrl(s.url)}" style="max-width:80px;border-radius:4px;cursor:pointer;" class="spImgThumb" data-src="${spImgUrl(s.url)}">`).join(''));
        const closed = t.status === 'CLOSED';
        $('#spReplyBox').toggle(!closed);
        $('#spClosedNote').toggle(closed);
    } catch(e) {}
}

async function spLoadMessages(number) {
    try {
        const json = await supportFetch(`${'/integrations/redirect/tickets/' + encodeURIComponent(number) + '/messages'}?phone=${encodeURIComponent(spPhone)}`);
        spRenderThread(json.data || []);
    } catch(e) {}
}

function spRenderThread(msgs) {
    const $t = $('#spThread').empty();
    msgs.forEach(m => {
        const side  = m.side === 'client' ? 'client' : 'staff';
        const label = side === 'client' ? 'You' : (m.authorName || 'Staff');
        const shots = (m.screenshots || []).map(s => `<img src="${spImgUrl(s.url)}" class="spImgThumb" data-src="${spImgUrl(s.url)}">`).join('');
        $t.append(`<div class="sp-bubble ${side}">
            <div class="fw-semibold small">${label}</div>
            <div>${m.message || ''}</div>
            ${shots}
            <div class="sp-meta">${spFmt(m.createdAt)}</div>
        </div>`);
    });
    $t.scrollTop($t[0].scrollHeight);
}

// ── Send Reply ──
$('#spSendReply').on('click', async function () {
    const msg = $('#spReplyText').val().trim();
    if (!spToken) { Swal.fire('Error', 'Raise a ticket first to establish support access', 'error'); return; }
    if (!msg && spReplyFiles.length === 0) { Swal.fire('Error', 'Enter a message or attach an image', 'error'); return; }
    const screenshots = spReplyFiles.length ? await filesToScreenshots(spReplyFiles) : [];
    if (screenshots === null) return;
    const body = {};
    if (msg) body.message = msg;
    if (screenshots.length) body.screenshots = screenshots;
    $(this).prop('disabled', true).text('Sending...');
    try {
        const json = await supportFetch('/integrations/redirect/tickets/' + encodeURIComponent(spOpenNumber) + '/messages?phone=' + encodeURIComponent(spPhone), {
            method: 'POST',
            body: body
        });
        if (json.success) {
            $('#spReplyText').val('');
            spReplyFiles = [];
            $('#spFileCount').text('');
            await spLoadMessages(spOpenNumber);
        } else {
            Swal.fire('Error', json.message || 'Failed to send', 'error');
        }
    } catch (e) { Swal.fire('Error', e.message || 'Failed to send reply', 'error'); }
    $(this).prop('disabled', false).text('Send');
});

$('#spReplyFiles').on('change', function () {
    spReplyFiles = Array.from(this.files).slice(0, 10);
    $('#spFileCount').text(spReplyFiles.length ? spReplyFiles.length + ' file(s)' : '');
});

// ── New Ticket ──
$('#spNewTicketBtn').on('click', function () {
    $('#ntSubject, #ntDesc').val('');
    $('#ntPriority').val('MEDIUM');
    $('#ntFiles').val('');
    $('#spNewTicketModal').modal('show');
});

$('#ntSubmitBtn').on('click', async function () {
    const subject = $('#ntSubject').val().trim();
    const desc    = $('#ntDesc').val().trim();
    if (!subject) { Swal.fire('Error', 'Subject is required', 'error'); return; }
    if (!desc)    { Swal.fire('Error', 'Description is required', 'error'); return; }
    const files = document.getElementById('ntFiles').files;
    const screenshots = files.length ? await filesToScreenshots(files) : [];
    if (screenshots === null) return;
    const body = { domain: SP_DOMAIN, customerName: spName, customerPhone: spPhone,
        subject, description: desc, priority: $('#ntPriority').val() };
    if (spEmail) body.customerEmail = spEmail;
    if (screenshots.length) body.screenshots = screenshots;
    $(this).prop('disabled', true).text('Submitting...');
    try {
        const json = await supportFetch('/integrations/redirect/tickets', { method: 'POST', body: body, withToken: false });
        const token = json.data?.accessToken;
        if (token && window.SupportLive) {
            SupportLive.saveToken(token);
            spToken = token;
            $('#spNewTicketModal').modal('hide');
            Swal.fire('Success', 'Ticket ' + (json.data.number || '') + ' raised', 'success');
            spPage = 1;
            await spLoadTickets();
        } else {
            Swal.fire('Error', 'Ticket was created, but the API did not return an access token.', 'error');
        }
    } catch (e) {
        const extra = e.errors ? ' ' + Object.values(e.errors).flat().join(' ') : '';
        Swal.fire('Error', (e.message || 'Failed to submit ticket') + extra, 'error');
    }
    $(this).prop('disabled', false).text('Submit Ticket');
});

// ── Filters & Pagination ──
$('#spSearchBtn').on('click', function () {
    spSearch = $('#spSearch').val().trim();
    spStatus = $('#spFilterStatus').val();
    spPriority = $('#spFilterPriority').val();
    spFromDate = $('#spFromDate').val();
    spToDate   = $('#spToDate').val();
    spPage = 1;
    spLoadTickets();
});

$('#spResetBtn').on('click', function () {
    $('#spSearch, #spFromDate, #spToDate').val('');
    $('#spFilterStatus, #spFilterPriority').val('');
    spSearch = spStatus = spPriority = spFromDate = spToDate = '';
    spPage = 1;
    spLoadTickets();
});

$('#spPrevBtn').on('click', function () { if (spPage > 1) { spPage--; spLoadTickets(); } });
$('#spNextBtn').on('click', function () { if (spTo < spTotal) { spPage++; spLoadTickets(); } });

$(document).on('click', '.spOpenTicket', function (e) {
    e.preventDefault();
    spOpenTicket($(this).data('number'));
});

$('#spBackBtn').on('click', function () {
    spOpenNumber = ''; spOpenTicketId = '';
    if (window.SupportLive) SupportLive.setOpenNumber('');
    spShowView('List');
    spLoadTickets();
});

$(document).on('click', '.spImgThumb', function () {
    $('#spImgFull').attr('src', $(this).data('src'));
    $('#spImgModal').modal('show');
});

window.spApplyLive = function (payload) {
    const sections = payload.sections || [];
    const number = payload.number || '';
    if (sections.includes('tickets')) spLoadTickets();
    if (number && number === spOpenNumber) {
        if (sections.includes('messages')) spLoadMessages(number);
        if (sections.includes('ticket')) spLoadTicketDetail(number);
    }
};

// ── Init ──
$(document).ready(function () {
    spCheckIdentity();
});
</script>
@endpush
