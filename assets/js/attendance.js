'use strict';

/* ============================================================
   Attendance Page — AJAX Load + Save
============================================================ */
(function () {
    console.log('[attendance] Script loaded');

    function init() {
        console.log('[attendance] Initializing...');

        const cfg = window.ATTENDANCE_CONFIG || {};
        const btnLoad = document.getElementById('btnLoadServants');
        const container = document.getElementById('attendanceContainer');

        if (!btnLoad) {
            console.warn('[attendance] btnLoadServants not found - not on attendance page?');
            return;
        }

        if (!container) {
            console.warn('[attendance] attendanceContainer not found');
            return;
        }

        /* ==========================================================
           زر "عرض الخدام"
           ========================================================== */
        btnLoad.addEventListener('click', async () => {
            const activityId = document.getElementById('filterActivity')?.value;
            const choirId    = document.getElementById('filterChoir')?.value;
            const date       = document.getElementById('filterDate')?.value;

            if (!activityId || !choirId || !date) {
                if (typeof toast === 'function') {
                    toast('يرجى اختيار النشاط والخورس والتاريخ', 'error');
                } else {
                    alert('يرجى اختيار النشاط والخورس والتاريخ');
                }
                return;
            }

            const origText = btnLoad.textContent;
            btnLoad.disabled = true;
            btnLoad.textContent = 'جاري التحميل...';
            container.innerHTML = '<div style="text-align:center;padding:40px"><div class="spinner spinner-lg"></div></div>';

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

                renderServantsUI({
                    activityId: parseInt(activityId, 10),
                    choirId:    parseInt(choirId, 10),
                    date:       date,
                    servants:   json.data.servants || [],
                    csrf:       cfg.csrf,
                });

            } catch (err) {
                console.error('[attendance] Load error:', err);
                container.innerHTML = `<div class="alert alert-error">${escapeHtml(err.message)}</div>`;
            } finally {
                btnLoad.disabled = false;
                btnLoad.textContent = origText;
            }
        });

        /* ==========================================================
           رسم واجهة الخدام
           ========================================================== */
        function renderServantsUI({ activityId, choirId, date, servants, csrf }) {
            if (!servants.length) {
                container.innerHTML = '<div class="empty-state">لا يوجد خدام نشطون في هذا الخورس</div>';
                return;
            }

            let rowsHtml = '';
            servants.forEach(s => {
                const curStatus = s.status || '';
                const curReason = s.excuse_reason || '';
                rowsHtml += `
                    <div class="attendance-row" data-servant-id="${s.servant_id}" data-name="${escapeHtml(s.name)}">
                        <div class="row-header">
                            <div class="avatar">${escapeHtml((s.name || '?').charAt(0))}</div>
                            <div class="servant-name">${escapeHtml(s.name)}</div>
                        </div>
                        <div class="status-group">
                            <button type="button" class="status-btn ${curStatus === 'present' ? 'on present' : ''}" data-status="present">✓ حاضر</button>
                            <button type="button" class="status-btn ${curStatus === 'absent' ? 'on absent' : ''}" data-status="absent">✕ غائب</button>
                            <button type="button" class="status-btn ${curStatus === 'excused' ? 'on excused' : ''}" data-status="excused">⏱ بعذر</button>
                        </div>
                        <div class="excuse-box" ${curStatus === 'excused' ? '' : 'hidden'}>
                            <input type="text" class="excuse-input" placeholder="سبب العذر..." value="${escapeHtml(curReason)}">
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
                        <button type="button" id="btnAllPresent" class="btn btn-primary">✓ تسجيل الكل حاضر</button>
                        <input type="search" id="servantSearch" class="search-input" placeholder="ابحث عن خادم...">
                    </div>
                    <div class="attendance-counter">
                        <span class="counter-chip present">حاضر: <b id="cntP">0</b></span>
                        <span class="counter-chip absent">غائب: <b id="cntA">0</b></span>
                        <span class="counter-chip excused">بعذر: <b id="cntE">0</b></span>
                    </div>
                    <div class="attendance-list">${rowsHtml}</div>
                    <div class="attendance-actions">
                        <button type="submit" class="btn btn-primary btn-lg">💾 حفظ الحضور</button>
                    </div>
                </form>
            `;

            bindRowEvents();
            updateCounters();
        }

        /* ==========================================================
           ربط أحداث الصفوف
           ========================================================== */
        function bindRowEvents() {
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
                    });
                });
            });

            document.getElementById('btnAllPresent')?.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.status-btn[data-status="present"]').forEach(b => b.click());
            });

            document.getElementById('servantSearch')?.addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                document.querySelectorAll('.attendance-row').forEach(r => {
                    r.style.display = (r.dataset.name || '').toLowerCase().includes(q) ? '' : 'none';
                });
                updateCounters();
            });

            document.getElementById('attendanceForm')?.addEventListener('submit', handleSave);
        }

        /* ==========================================================
           عدّادات
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

        /* ==========================================================
           حفظ
           ========================================================== */
        async function handleSave(e) {
            e.preventDefault();
            const form = e.target;

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
                else alert('يرجى تحديد حالة كل خادم وكتابة أسباب الأعذار');
                return;
            }
            if (!records.length) {
                if (typeof toast === 'function') toast('لا يوجد خدام', 'error');
                return;
            }

            const btn = form.querySelector('button[type="submit"]');
            const orig = btn.textContent;
            btn.disabled = true;
            btn.textContent = '⏳ جاري الحفظ...';

            const csrf = window.ATTENDANCE_CONFIG?.csrf
                       || form.querySelector('input[name="_csrf_token"]')?.value
                       || document.querySelector('meta[name="csrf-token"]')?.content
                       || '';

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
                        activity_id: parseInt(form.dataset.activity, 10),
                        choir_id:    parseInt(form.dataset.choir, 10),
                        date:        form.dataset.date,
                        records:     records
                    })
                });
                const j = await res.json();

                if (j.success) {
                    if (typeof toast === 'function') toast('✅ ' + (j.message || 'تم الحفظ'), 'success');
                    else alert('✅ ' + (j.message || 'تم الحفظ'));
                } else {
                    if (typeof toast === 'function') toast('❌ ' + (j.message || 'فشل'), 'error');
                    else alert('❌ ' + (j.message || 'فشل'));
                }
            } catch (err) {
                console.error('[attendance] Save error:', err);
                if (typeof toast === 'function') toast('خطأ: ' + err.message, 'error');
                else alert('خطأ: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = orig;
            }
        }

        /* ==========================================================
           Helpers
           ========================================================== */
        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        console.log('[attendance] ✅ Ready');
    }

    /* ============================================================
       Run
       ============================================================ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();