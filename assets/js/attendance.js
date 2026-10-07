'use strict';

(function () {
    console.log('[attendance] Loaded');

    function init() {
        const cfg = window.ATTENDANCE_CONFIG || {};
        const btnLoad = document.getElementById('btnLoadServants');
        const container = document.getElementById('attendanceContainer');
        const sessionBar = document.getElementById('sessionInfo');

        if (!btnLoad || !container) return;

        /* ==========================================================
           Load Servants
           ========================================================== */
        btnLoad.addEventListener('click', async () => {
            const activityId = document.getElementById('filterActivity')?.value;
            const choirId    = document.getElementById('filterChoir')?.value;
            const date       = document.getElementById('filterDate')?.value;

            if (!activityId || !choirId || !date) {
                if (typeof toast === 'function') toast('اختر النشاط والخورس والتاريخ', 'error');
                return;
            }

            const orig = btnLoad.textContent;
            btnLoad.disabled = true;
            btnLoad.textContent = 'جاري التحميل...';
            container.innerHTML = '<div class="attendance-loading">⏳ جاري التحميل...</div>';

            try {
                const url = `${cfg.baseUrl}/api/attendance/session?activity_id=${activityId}&choir_id=${choirId}&date=${date}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const json = await res.json();

                if (!json.success) {
                    container.innerHTML = `<div class="alert alert-error">${escapeHtml(json.message || 'خطأ')}</div>`;
                    return;
                }

                // Session info bar
                const actName = cfg.activities.find(a => a.id == activityId)?.name || '—';
                const choirName = cfg.choirs.find(c => c.id == choirId)?.name || '—';
                document.getElementById('infoActivity').textContent = actName;
                document.getElementById('infoChoir').textContent = choirName;
                document.getElementById('infoDate').textContent = formatDate(date);
                if (sessionBar) sessionBar.style.display = '';

                renderServantsUI({
                    activityId: parseInt(activityId, 10),
                    choirId:    parseInt(choirId, 10),
                    date:       date,
                    servants:   json.data.servants || [],
                    csrf:       cfg.csrf,
                });

            } catch (err) {
                console.error(err);
                container.innerHTML = `<div class="alert alert-error">${escapeHtml(err.message)}</div>`;
            } finally {
                btnLoad.disabled = false;
                btnLoad.textContent = orig;
            }
        });

        /* ==========================================================
           Render UI
           ========================================================== */
        function renderServantsUI({ activityId, choirId, date, servants, csrf }) {
            if (!servants.length) {
                container.innerHTML = '<div class="empty-state">لا يوجد خدام نشطون في هذا الخورس</div>';
                return;
            }

            let rowsHtml = '';
            servants.forEach(s => {
                const cur = s.status || 'absent'; // ← الافتراضي غائب
                const reason = s.excuse_reason || '';
                rowsHtml += `
                    <div class="attendance-row ${cur ? 'status-' + cur : ''}" 
                         data-servant-id="${s.servant_id}" 
                         data-name="${escapeHtml(s.name)}">
                        <div class="row-header">
                            <div class="avatar">${escapeHtml((s.name || '?').charAt(0))}</div>
                            <div class="servant-name">${escapeHtml(s.name)}</div>
                        </div>
                        <div class="status-group">
                            <button type="button" class="status-btn ${cur === 'present' ? 'on present' : ''}" data-status="present">
                                <span class="icon">✓</span> حاضر
                            </button>
                            <button type="button" class="status-btn ${cur === 'absent' ? 'on absent' : ''}" data-status="absent">
                                <span class="icon">✕</span> غائب
                            </button>
                            <button type="button" class="status-btn ${cur === 'excused' ? 'on excused' : ''}" data-status="excused">
                                <span class="icon">⏱</span> بعذر
                            </button>
                        </div>
                        <div class="excuse-box" ${cur === 'excused' ? '' : 'hidden'}>
                            <input type="text" class="excuse-input" placeholder="سبب العذر..." value="${escapeHtml(reason)}">
                        </div>
                    </div>
                `;
            });

            container.innerHTML = `
                <form id="attendanceForm"
                      data-activity="${activityId}"
                      data-choir="${choirId}"
                      data-date="${escapeHtml(date)}">

                    <div class="attendance-toolbar">
                        <button type="button" id="btnAllPresent" class="btn btn-primary">
                            ✓ تسجيل الكل حاضر
                        </button>
                        <input type="search" id="servantSearch" class="search-input" placeholder="ابحث عن خادم...">
                    </div>

                    <div class="attendance-counter">
                        <span class="counter-chip present">✓ حاضر: <b id="cntP">0</b></span>
                        <span class="counter-chip absent">✕ غائب: <b id="cntA">0</b></span>
                        <span class="counter-chip excused">⏱ بعذر: <b id="cntE">0</b></span>
                    </div>

                    <div class="attendance-list">${rowsHtml}</div>

                    <div class="attendance-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 حفظ الحضور
                        </button>
                    </div>
                </form>
            `;

            bindEvents(csrf, activityId, choirId, date);
            updateCounters();
            updateRowStatus();
        }

        /* ==========================================================
           Bind Events
           ========================================================== */
        function bindEvents(csrf, activityId, choirId, date) {
            // Status buttons
            document.querySelectorAll('.attendance-row').forEach(row => {
                const btns = row.querySelectorAll('.status-btn');
                const excuseBox = row.querySelector('.excuse-box');

                btns.forEach(b => {
                    b.addEventListener('click', (e) => {
                        e.preventDefault();
                        btns.forEach(x => x.classList.remove('on', 'present', 'absent', 'excused'));
                        const st = b.dataset.status;
                        b.classList.add('on', st);
                        if (st === 'excused') {
                            excuseBox.hidden = false;
                            excuseBox.querySelector('input')?.focus();
                        } else {
                            excuseBox.hidden = true;
                            const inp = excuseBox.querySelector('input');
                            if (inp) inp.value = '';
                        }
                        updateCounters();
                        updateRowStatus();
                    });
                });
            });

            // Mark all present
            document.getElementById('btnAllPresent')?.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.status-btn[data-status="present"]').forEach(b => b.click());
            });

            // Search
            document.getElementById('servantSearch')?.addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                document.querySelectorAll('.attendance-row').forEach(r => {
                    r.style.display = (r.dataset.name || '').toLowerCase().includes(q) ? '' : 'none';
                });
                updateCounters();
            });

            // Submit
            document.getElementById('attendanceForm')?.addEventListener('submit', async (e) => {
                e.preventDefault();
                await saveAttendance({ csrf, activityId, choirId, date });
            });
        }

        /* ==========================================================
           Save
           ========================================================== */
        async function saveAttendance({ csrf, activityId, choirId, date }) {
            const records = [];
            let hasError = false;

            document.querySelectorAll('.attendance-row').forEach(row => {
                if (row.style.display === 'none') return;
                const active = row.querySelector('.status-btn.on');
                if (!active) { hasError = true; row.classList.add('row-error'); return; }
                const st = active.dataset.status;
                let reason = null;
                if (st === 'excused') {
                    const inp = row.querySelector('.excuse-input');
                    reason = inp ? inp.value.trim() : '';
                    if (!reason) { hasError = true; row.classList.add('row-error'); return; }
                }
                row.classList.remove('row-error');
                records.push({
                    servant_id: parseInt(row.dataset.servantId, 10),
                    status: st,
                    excuse_reason: reason
                });
            });

            if (hasError) {
                if (typeof toast === 'function') toast('يرجى تحديد حالة كل خادم وكتابة أسباب الأعذار', 'error');
                return;
            }
            if (!records.length) {
                if (typeof toast === 'function') toast('لا يوجد خدام', 'error');
                return;
            }

            const btn = document.querySelector('#attendanceForm button[type="submit"]');
            const orig = btn.textContent;
            btn.disabled = true;
            btn.textContent = '⏳ جاري الحفظ...';

            try {
                const res = await fetch(`${cfg.baseUrl}/api/attendance/save`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': csrf
                    },
                    body: JSON.stringify({
                        activity_id: activityId,
                        choir_id: choirId,
                        date: date,
                        records: records
                    })
                });
                const j = await res.json();

                if (j.success) {
                    if (typeof toast === 'function') toast('✅ ' + (j.message || 'تم الحفظ'), 'success');
                } else {
                    if (typeof toast === 'function') toast('❌ ' + (j.message || 'فشل'), 'error');
                }
            } catch (err) {
                console.error(err);
                if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = orig;
            }
        }

        /* ==========================================================
           Helpers
           ========================================================== */
        function updateCounters() {
            const c = { present: 0, absent: 0, excused: 0 };
            document.querySelectorAll('.attendance-row').forEach(r => {
                if (r.style.display === 'none') return;
                const a = r.querySelector('.status-btn.on');
                if (a) c[a.dataset.status]++;
            });
            const p = document.getElementById('cntP');
            const ab = document.getElementById('cntA');
            const ex = document.getElementById('cntE');
            if (p) p.textContent = c.present;
            if (ab) ab.textContent = c.absent;
            if (ex) ex.textContent = c.excused;
        }

        function updateRowStatus() {
            document.querySelectorAll('.attendance-row').forEach(row => {
                row.classList.remove('status-present', 'status-absent', 'status-excused');
                const a = row.querySelector('.status-btn.on');
                if (a) row.classList.add('status-' + a.dataset.status);
            });
        }

        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, ch => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[ch]));
        }

        function formatDate(iso) {
            const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            const [y, m, d] = iso.split('-').map(Number);
            return `${d} ${months[m - 1]} ${y}`;
        }

        console.log('[attendance] ✅ Ready');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();