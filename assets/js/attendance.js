'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('attendanceForm');
    if (!form) return;

    const rows = () => Array.from(document.querySelectorAll('.attendance-row'));

    /* ------------- اختيار الحالة ------------- */
    document.querySelectorAll('.attendance-row').forEach(row => {
        const buttons = row.querySelectorAll('.status-btn');
        const excuseBox = row.querySelector('.excuse-box');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('on', 'present', 'absent', 'excused'));
                const s = btn.dataset.status;
                btn.classList.add('on', s);
                if (s === 'excused') {
                    excuseBox.hidden = false;
                    excuseBox.querySelector('input').focus();
                } else {
                    excuseBox.hidden = true;
                }
            });
        });
    });

    /* ------------- تسجيل الكل حاضر ------------- */
    document.getElementById('btnAllPresent')?.addEventListener('click', () => {
        rows().forEach(row => {
            const p = row.querySelector('.status-btn[data-status="present"]');
            p?.click();
        });
        toast('تم تحديد كل الخدام كحاضرين', 'info');
    });

    /* ------------- البحث ------------- */
    const search = document.getElementById('servantSearch');
    search?.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        rows().forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            row.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    });

    /* ------------- الحفظ ------------- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const records = [];
        let hasError = false;

        rows().forEach(row => {
            if (row.style.display === 'none') return;
            const sid = parseInt(row.dataset.servantId, 10);
            const active = row.querySelector('.status-btn.on');
            if (!active) {
                hasError = true;
                return;
            }
            const status = active.dataset.status;
            let reason = null;
            if (status === 'excused') {
                const input = row.querySelector('.excuse-input');
                reason = input ? input.value.trim() : '';
                if (!reason) {
                    hasError = true;
                    row.classList.add('row-error');
                    return;
                }
            }
            row.classList.remove('row-error');
            records.push({ servant_id: sid, status, excuse_reason: reason });
        });

        if (hasError) {
            toast('يرجى استكمال بيانات الحضور أو أسباب الأعذار', 'error');
            return;
        }
        if (records.length === 0) {
            toast('لا يوجد خدام لتسجيل حضورهم', 'error');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الحفظ...';

        try {
            const url = form.dataset.url || (window.APP_URL + '/attendance/save');
            const csrf = form.querySelector('input[name="_csrf_token"]')?.value || '';
            const data = await api(url, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf },
                body: {
                    activity_id: parseInt(form.dataset.activity, 10),
                    choir_id:    parseInt(form.dataset.choir, 10),
                    date:        form.dataset.date,
                    records,
                    _csrf_token: csrf,
                },
            });
            toast(data.message || 'تم الحفظ بنجاح', 'success');
        } catch (err) {
            toast(err.message || 'حدث خطأ أثناء الحفظ', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'حفظ الحضور';
        }
    });
});