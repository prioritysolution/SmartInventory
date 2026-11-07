(function () {
    const cfg = window.SUPPORT_CFG || {};
    const API = cfg.api || '';
    const KEY = cfg.key || '';
    const PHONE = cfg.phone || '';
    const DOMAIN = window.location.host;
    const UNREAD_KEY = 'supportUnread';
    const NOTICES_KEY = 'supportNotices';
    const TOKEN_KEY = 'supportToken';
    const PHONE_KEY = 'supportPhone';
    const DOMAIN_KEY = 'supportDomain';
    const EXPIRY_KEY = 'supportExpiry';

    function readJson(key, fallback) {
        try {
            const value = JSON.parse(localStorage.getItem(key) || '');
            return value == null ? fallback : value;
        } catch (e) {
            return fallback;
        }
    }

    const unread = new Set(readJson(UNREAD_KEY, []));
    let notices = readJson(NOTICES_KEY, []);
    let openNumber = '';
    let echo = null;
    let customerChannelName = '';
    let ticketChannelName = '';
    let soundUrl = '';
    let audio = null;
    let audioUnlocked = false;
    const waiting = new Set();
    const soundQueue = [];
    let timer = 0;
    let pendingNumber = '';

    function token() {
        const exp = parseInt(localStorage.getItem(EXPIRY_KEY) || '0', 10);
        const savedPhone = localStorage.getItem(PHONE_KEY) || '';
        const savedDomain = localStorage.getItem(DOMAIN_KEY) || '';
        if (!exp || Date.now() >= exp || savedPhone !== PHONE || savedDomain !== DOMAIN) {
            return '';
        }
        return localStorage.getItem(TOKEN_KEY) || '';
    }

    function remember() {
        localStorage.setItem(UNREAD_KEY, JSON.stringify([...unread]));
        localStorage.setItem(NOTICES_KEY, JSON.stringify(notices.slice(0, 20)));
        paint();
    }

    function countLabel() {
        const count = unread.size;
        return count > 9 ? '9+' : String(count);
    }

    function paint() {
        const count = unread.size;
        const label = countLabel();
        document.querySelectorAll('[data-support-icon], [data-support-bell]').forEach(function (icon) {
            icon.dataset.count = label;
            icon.classList.toggle('has-news', count > 0);
            const badge = icon.querySelector('.sp-live-count');
            if (badge) badge.textContent = label;
        });
        const icon = document.querySelector('[data-support-icon]');
        if (icon) icon.setAttribute('aria-label', count > 0 ? 'Support, ' + count + ' new' : 'Support');
        paintBell();
    }

    function paintBell() {
        const list = document.getElementById('supportBellList');
        if (!list) return;
        if (!notices.length) {
            list.innerHTML = '<div class="px-3 py-4 text-center text-muted small">No notifications yet</div>';
            return;
        }
        list.innerHTML = notices.map(function (row) {
            const when = new Date(row.at).toLocaleString();
            const title = row.title || 'Support update';
            const body = row.body ? '<div class="text-muted small">' + escapeHtml(row.body) + '</div>' : '';
            const dot = row.unread ? '<span class="sp-bell-dot"></span>' : '';
            return '<button type="button" class="dropdown-item text-wrap border-bottom py-2 sp-bell-row" data-number="' + escapeHtml(row.number) + '">' +
                '<div class="d-flex justify-content-between"><strong class="small">' + escapeHtml(title) + '</strong>' + dot + '</div>' +
                '<div class="small">' + escapeHtml(row.number || '') + '</div>' + body +
                '<div class="text-muted" style="font-size:11px;">' + escapeHtml(when) + '</div></button>';
        }).join('');
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function noticeText(value, max) {
        if (typeof value !== 'string') return '';
        const text = value.replace(/[\u0000-\u001f]/g, ' ').replace(/\s+/g, ' ').trim();
        if (!text || /data:image|\/storage\//i.test(text)) return '';
        return text.slice(0, max);
    }

    function clearTicket(number) {
        if (!number) return;
        unread.delete(number);
        notices = notices.filter(function (item) { return item.number !== number; });
        remember();
    }

    function markAllRead() {
        unread.clear();
        notices = notices.map(function (item) {
            item.unread = false;
            return item;
        });
        remember();
    }

    function pushNotice(event) {
        const number = event.number || '';
        const row = {
            id: number || String(event.ticketId || Date.now()),
            number: number,
            title: noticeText(event.title, 120),
            body: noticeText(event.body, 240),
            at: Date.now(),
            unread: true
        };
        notices = [row].concat(notices.filter(function (item) { return item.id !== row.id; })).slice(0, 20);
    }

    function showToast(title, body, onOpen) {
        let toast = document.getElementById('supportToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'supportToast';
            toast.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:2000;max-width:320px;background:#fff;border:1px solid #dee2e6;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:12px 14px;display:none;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<div class="fw-semibold">' + escapeHtml(title) + '</div>' +
            (body ? '<div class="small text-muted mt-1">' + escapeHtml(body) + '</div>' : '') +
            '<button type="button" class="btn btn-primary btn-sm mt-2" id="supportToastOpen">Open ticket</button>';
        toast.style.display = 'block';
        const btn = document.getElementById('supportToastOpen');
        if (btn) btn.onclick = function () {
            toast.style.display = 'none';
            onOpen();
        };
        setTimeout(function () { if (toast) toast.style.display = 'none'; }, 8000);
    }

    function chime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.05;
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) {}
    }

    function playSound() {
        if (!audioUnlocked) return;
        if (audio) {
            audio.currentTime = 0;
            audio.play().catch(function () { chime(); });
            return;
        }
        chime();
    }

    function presentNotice(event) {
        const title = noticeText(event.title, 120) || 'Support update';
        const body = noticeText(event.body, 240);
        const hidden = document.visibilityState === 'hidden';
        const canNotify = hidden && typeof Notification !== 'undefined' && Notification.permission === 'granted';
        if (canNotify) {
            const popup = new Notification(title, {
                body: body || undefined,
                tag: event.number || 'support',
                renotify: true,
                silent: false,
                icon: cfg.icon || undefined
            });
            popup.onclick = function () {
                window.focus();
                popup.close();
                openFromNotice(event.number);
            };
            return;
        }
        playSound();
        showToast(title, body, function () { openFromNotice(event.number); });
    }

    function openFromNotice(number) {
        if (!number) return;
        clearTicket(number);
        setOpenNumber(number);
        if (window.location.pathname.replace(/\/$/, '').endsWith('/support') && typeof window.spOpenTicket === 'function') {
            window.spOpenTicket(number);
            return;
        }
        window.location.href = (cfg.supportUrl || '/support') + '?ticket=' + encodeURIComponent(number);
    }

    function notifyPage() {
        if (typeof window.spApplyLive !== 'function') return;
        const sections = [...waiting];
        const number = pendingNumber;
        window.spApplyLive({ sections: sections, number: number });
    }

    function onSignal(event) {
        (event.refresh || []).forEach(function (section) { waiting.add(section); });
        if (event.number) pendingNumber = event.number;
        const lookingAtIt = Boolean(event.number) && event.number === openNumber;
        if (event.sound && event.number && !lookingAtIt) {
            unread.add(event.number);
            pushNotice(event);
            remember();
            presentNotice(event);
            waiting.add('tickets');
        }
        if (lookingAtIt) {
            clearTicket(event.number);
            waiting.add('messages');
            waiting.add('ticket');
            waiting.add('tickets');
        }
        clearTimeout(timer);
        timer = setTimeout(function () {
            notifyPage();
            waiting.clear();
        }, 300);
    }

    async function supportFetch(path, options) {
        options = options || {};
        const headers = {
            Accept: 'application/json',
            'X-Api-Key': KEY
        };
        if (options.body) headers['Content-Type'] = 'application/json';
        if (options.token) headers['X-Client-Token'] = options.token;
        const response = await fetch(API + path, {
            method: options.method || 'GET',
            credentials: 'omit',
            headers: headers,
            body: options.body || undefined
        });
        const body = await response.json().catch(function () { return null; });
        if (!response.ok || !body || body.success === false) {
            const error = new Error((body && body.message) || 'The request could not be completed.');
            error.status = response.status;
            throw error;
        }
        return body;
    }

    function leaveTicket() {
        if (ticketChannelName && echo) {
            try { echo.leave(ticketChannelName); } catch (e) {}
        }
        ticketChannelName = '';
    }

    async function joinTicket(number) {
        leaveTicket();
        const saved = token();
        if (!echo || !number || !saved) return;
        try {
            const json = await supportFetch(
                '/integrations/redirect/realtime?phone=' + encodeURIComponent(PHONE) + '&number=' + encodeURIComponent(number),
                { token: saved }
            );
            if (!json.data.ticketChannel) return;
            ticketChannelName = json.data.ticketChannel;
            echo.private(ticketChannelName).listen('.signal', onSignal);
        } catch (e) {}
    }

    async function connect() {
        const saved = token();
        if (!saved || !PHONE || !API || !KEY || echo) return;
        try {
            const json = await supportFetch(
                '/integrations/redirect/realtime?phone=' + encodeURIComponent(PHONE),
                { token: saved }
            );
            const d = json.data;
            soundUrl = d.soundUrl || '';
            if (soundUrl) audio = new Audio(soundUrl);
            const EchoLib = window.Echo || window.LaravelEcho;
            if (!window.Pusher || !EchoLib) return;
            const broadcaster = d.broadcaster === 'reverb' ? 'pusher' : (d.broadcaster || 'pusher');
            const client = new EchoLib({
                broadcaster: broadcaster,
                key: d.key,
                wsHost: d.host,
                wsPort: Number(d.port),
                wssPort: Number(d.port),
                forceTLS: d.scheme === 'https',
                encrypted: d.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
                cluster: 'mt1',
                authorizer: function (channel) {
                    return {
                        authorize: function (socketId, callback) {
                            fetch(d.authEndpoint, {
                                method: 'POST',
                                credentials: 'omit',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Api-Key': KEY,
                                    'X-Client-Token': token()
                                },
                                body: JSON.stringify({
                                    socket_id: socketId,
                                    channel_name: channel.name,
                                    phone: PHONE
                                })
                            }).then(function (r) { return r.json(); }).then(function (body) {
                                if (body && body.auth) callback(null, { auth: body.auth });
                                else callback(new Error('Realtime auth failed'), null);
                            }).catch(function (err) { callback(err, null); });
                        }
                    };
                }
            });
            customerChannelName = d.customerChannel;
            client.private(customerChannelName).listen('.signal', onSignal);
            echo = client;
            if (openNumber) joinTicket(openNumber);
        } catch (e) {
            echo = null;
        }
    }

    function saveToken(value) {
        localStorage.setItem(TOKEN_KEY, value);
        localStorage.setItem(PHONE_KEY, PHONE);
        localStorage.setItem(DOMAIN_KEY, DOMAIN);
        localStorage.setItem(EXPIRY_KEY, String(Date.now() + 30 * 24 * 3600 * 1000));
        connect();
    }

    function clearSession() {
        [TOKEN_KEY, PHONE_KEY, DOMAIN_KEY, EXPIRY_KEY, UNREAD_KEY, NOTICES_KEY].forEach(function (key) {
            localStorage.removeItem(key);
        });
        unread.clear();
        notices = [];
        if (echo) {
            try { echo.disconnect(); } catch (e) {}
            echo = null;
        }
        paint();
    }

    function setOpenNumber(number) {
        openNumber = number || '';
        if (openNumber) joinTicket(openNumber);
        else leaveTicket();
    }

    document.addEventListener('pointerdown', function () {
        audioUnlocked = true;
        if (audio) {
            audio.play().then(function () {
                audio.pause();
                audio.currentTime = 0;
            }).catch(function () {});
        }
        if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
            Notification.requestPermission().catch(function () {});
        }
    }, { once: true });

    document.addEventListener('click', function (event) {
        const row = event.target.closest('.sp-bell-row');
        if (row) {
            event.preventDefault();
            openFromNotice(row.getAttribute('data-number'));
            return;
        }
        if (event.target.closest('#supportMarkAll')) {
            event.preventDefault();
            markAllRead();
            return;
        }
        if (event.target.closest('a.logout')) {
            clearSession();
        }
    });

    window.SupportLive = {
        domain: DOMAIN,
        phone: PHONE,
        token: token,
        saveToken: saveToken,
        clearSession: clearSession,
        unreadHas: function (number) { return unread.has(number); },
        clearTicket: clearTicket,
        setOpenNumber: setOpenNumber,
        connect: connect
    };

    paint();
    connect();
})();
