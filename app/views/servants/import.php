<div class="page-header">
    <div class="page-header-text">
        <h1>استيراد الخدام من Excel</h1>
        <p>حمّل القالب، املأه، ثم ارفعه</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= e(appBaseUrl()) ?>/servants" class="btn">← رجوع</a>
    </div>
</div>

<!-- الخُوَرَس المتاحة -->
<div class="card" style="margin-bottom:20px;background:var(--info-soft);border-color:var(--info)">
    <div style="display:flex;align-items:flex-start;gap:14px">
        <div style="font-size:32px;flex-shrink:0">ℹ️</div>
        <div style="flex:1">
            <div style="font-weight:800;font-size:15px;color:var(--info-text);margin-bottom:8px">
                الخُوَرَس المتاحة في النظام
            </div>
            <div style="font-size:13px;color:var(--info-text);line-height:1.6;margin-bottom:12px">
                استخدم <strong>أحد هذه الأسماء بالضبط</strong> في عمود "الخورس":
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <?php if (empty($choirs)): ?>
                    <span style="color:var(--info-text);font-weight:700">⚠️ لا توجد خُوَرَس بعد — أضف خورساً أولاً</span>
                <?php else: ?>
                    <?php foreach ($choirs as $c): ?>
                        <span class="badge" style="font-size:14px;padding:6px 14px;background:#fff;border:2px solid var(--info);color:var(--info-text)">
                            🎵 <?= e($c['name']) ?>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- الخطوة 1 -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div>
            <div class="card-title">📥 الخطوة 1: حمّل القالب</div>
            <div class="card-subtitle">ملف CSV يفتح في Excel بالعربية</div>
        </div>
    </div>
    <p style="color:var(--text-muted);margin:0 0 16px;font-size:14px">
        حمّل القالب، املأ بيانات الخدام، ثم ارفعه. <strong>الكود يُولَّد تلقائياً</strong> — لا تحتاج كتابته.
    </p>
    <a href="<?= e(appBaseUrl()) ?>/servants/import/template" class="btn btn-primary">
        ⬇️ تحميل القالب
    </a>
</div>

<!-- الخطوة 2 -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div>
            <div class="card-title">📤 الخطوة 2: ارفع الملف</div>
            <div class="card-subtitle">حدّد الملف المملوء</div>
        </div>
    </div>

    <div id="dropZone" style="border:2px dashed var(--border-strong);border-radius:var(--radius-lg);padding:40px 20px;text-align:center;cursor:pointer;transition:all .2s;background:var(--bg-subtle)">
        <div style="font-size:48px;margin-bottom:12px">📁</div>
        <div style="font-weight:800;font-size:16px;color:var(--text-primary);margin-bottom:6px">
            اسحب الملف هنا أو انقر للاختيار
        </div>
        <div style="font-size:13px;color:var(--text-muted)">CSV فقط — بحد أقصى 2 ميجابايت</div>
        <input type="file" id="fileInput" accept=".csv,text/csv" style="display:none">
    </div>

    <div id="fileInfo" style="display:none;margin-top:16px;padding:14px 18px;background:var(--primary-soft);border:1px solid var(--primary);border-radius:var(--radius)">
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">📄</span>
            <div style="flex:1">
                <div id="fileName" style="font-weight:800"></div>
                <div id="fileSize" style="font-size:12px;color:var(--text-muted)"></div>
            </div>
            <button type="button" class="btn btn-sm" onclick="resetFile()">✕</button>
        </div>
    </div>

    <div style="margin-top:16px">
        <button type="button" class="btn btn-primary" id="btnParse" disabled>
            📊 تحليل الملف
        </button>
    </div>
</div>

<!-- الخطوة 3 -->
<div id="previewSection" style="display:none">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👁️ الخطوة 3: راجع البيانات</div>
                <div class="card-subtitle" id="previewSubtitle"></div>
            </div>
            <div id="previewStats" style="display:flex;gap:8px;flex-wrap:wrap"></div>
        </div>

        <div id="previewErrors"></div>

        <div style="overflow:auto;max-height:500px;border-radius:var(--radius);border:1px solid var(--border-color)">
            <table class="table" style="margin:0;min-width:800px">
                <thead id="previewHead" style="position:sticky;top:0;z-index:1"></thead>
                <tbody id="previewBody"></tbody>
            </table>
        </div>

        <div class="form-actions" style="margin-top:20px">
            <button type="button" class="btn btn-primary" id="btnImport" disabled>
                ✅ استيراد
            </button>
            <button type="button" class="btn" onclick="resetAll()">
                🔄 رفع ملف آخر
            </button>
        </div>
    </div>
</div>

<style>
#dropZone.dragover {
    border-color: var(--primary);
    background: var(--primary-soft);
    transform: scale(1.01);
}
.row-error { background: var(--danger-soft) !important; }
.row-warning { background: var(--warning-soft) !important; }
.row-success { background: var(--success-soft) !important; }
</style>

<script>
var currentFile = null;
var parsedData = null;

var dropZone = document.getElementById('dropZone');
var fileInput = document.getElementById('fileInput');

dropZone.addEventListener('click', function() { fileInput.click(); });
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', function() { dropZone.classList.remove('dragover'); });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
});
fileInput.addEventListener('change', function() {
    if (this.files.length) handleFile(this.files[0]);
});

function handleFile(file) {
    if (!file.name.toLowerCase().endsWith('.csv')) {
        toast('يجب أن يكون الملف CSV', 'error');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        toast('حجم الملف أكبر من 2 ميجابايت', 'error');
        return;
    }

    currentFile = file;
    document.getElementById('fileInfo').style.display = '';
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('btnParse').disabled = false;
    document.getElementById('previewSection').style.display = 'none';
}

function resetFile() {
    currentFile = null;
    fileInput.value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('btnParse').disabled = true;
    document.getElementById('previewSection').style.display = 'none';
}

function resetAll() {
    resetFile();
    parsedData = null;
}

document.getElementById('btnParse').addEventListener('click', async function() {
    if (!currentFile) return;
    var btn = this;
    var orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ جاري التحليل...';

    try {
        var formData = new FormData();
        formData.append('file', currentFile);

        var csrf = document.querySelector('meta[name="csrf-token"]').content;

        var res = await fetch('/api/servants/import/parse', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf },
            body: formData
        });
        var j = await res.json();

        if (!j.success) {
            toast('❌ ' + (j.message || 'فشل التحليل'), 'error');
            return;
        }
        parsedData = j.data;
        renderPreview(j.data);
        toast('✅ تم تحليل الملف', 'success');
    } catch (err) {
        toast('خطأ: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = orig;
    }
});

function renderPreview(data) {
    document.getElementById('previewSection').style.display = '';

    var stats = document.getElementById('previewStats');
    stats.innerHTML =
        '<span class="badge badge-success">✓ ' + data.valid_count + ' جاهز</span>' +
        (data.error_count > 0 ? '<span class="badge badge-danger">✕ ' + data.error_count + ' خطأ</span>' : '') +
        (data.warning_count > 0 ? '<span class="badge badge-warning">⚠ ' + data.warning_count + ' تحذير</span>' : '') +
        (data.skipped_rows > 0 ? '<span class="badge badge-neutral">⊘ ' + data.skipped_rows + ' متخطى</span>' : '');

    document.getElementById('previewSubtitle').textContent = data.total_rows + ' صف في الملف';

    document.getElementById('previewHead').innerHTML = '<tr>' +
        '<th>#</th>' +
        '<th>الاسم</th>' +
        '<th>الخورس</th>' +
        '<th>الهاتف</th>' +
        '<th>تاريخ الانضمام</th>' +
        '<th>اسم المستخدم</th>' +
        '<th>كلمة المرور</th>' +
        '<th>الإيموجي</th>' +
        '<th>الحالة</th>' +
        '</tr>';

    var html = '';
    data.rows.forEach(function(r, i) {
        var rowClass = r.status === 'error' ? 'row-error' :
                       r.status === 'warning' ? 'row-warning' : 'row-success';

        var badge = '';
        if (r.status === 'error') badge = '<span class="badge badge-danger">✕ ' + (r.messages || []).join('، ') + '</span>';
        else if (r.status === 'warning') badge = '<span class="badge badge-warning">⚠ ' + (r.messages || []).join('، ') + '</span>';
        else badge = '<span class="badge badge-success">✓ جاهز</span>';

        html += '<tr class="' + rowClass + '">' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + escapeHtml(r.name || '') + '</td>' +
            '<td>' + escapeHtml(r.choir_name || '—') + '</td>' +
            '<td dir="ltr">' + escapeHtml(r.phone || '—') + '</td>' +
            '<td>' + escapeHtml(r.join_date || '—') + '</td>' +
            '<td><code>' + escapeHtml(r.username || '') + '</code></td>' +
            '<td><code>' + escapeHtml(r.password || '') + '</code></td>' +
            '<td style="font-size:20px">' + (r.emoji || '👤') + '</td>' +
            '<td>' + badge + '</td>' +
            '</tr>';
    });
    document.getElementById('previewBody').innerHTML = html;

    var errors = document.getElementById('previewErrors');
    if (data.general_errors && data.general_errors.length) {
        errors.innerHTML = '<div class="alert alert-error"><ul>' +
            data.general_errors.map(function(e) { return '<li>' + escapeHtml(e) + '</li>'; }).join('') +
            '</ul></div>';
    } else {
        errors.innerHTML = '';
    }

    var btnImport = document.getElementById('btnImport');
    btnImport.disabled = (data.valid_count === 0);
    btnImport.textContent = '✅ استيراد ' + data.valid_count + ' خادم';
}

document.getElementById('btnImport').addEventListener('click', async function() {
    if (!parsedData) return;

    var ok = await showConfirm({
        title: 'تأكيد الاستيراد',
        message: 'سيتم استيراد ' + parsedData.valid_count + ' خادم.',
        extra: '<div style="padding:10px;background:var(--primary-soft);border-radius:8px;font-size:13px;line-height:1.6">' +
               '✅ سيُنشأ لكل خادم حساب دخول تلقائياً<br>' +
               '✅ الأكواد تُولَّد تلقائياً (S001, S002, ...)<br>' +
               '✅ يمكن للخادم تسجيل الدخول ورؤية ملفه وسجل حضوره' +
               '</div>',
        confirmText: 'استيراد',
    });

    if (!ok) return;

    var btn = this;
    var orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ جاري الاستيراد...';

    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        var res = await fetch('/api/servants/import/confirm', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf
            },
            body: JSON.stringify({ rows: parsedData.rows })
        });
        var j = await res.json();

        if (j.success) {
            toast('✅ ' + j.message, 'success');
            setTimeout(function() {
                window.location.href = window.APP_URL + '/servants';
            }, 1500);
        } else {
            var msg = j.message;
            if (j.errors && Object.keys(j.errors).length) {
                msg += ' — ' + Object.values(j.errors).join('، ');
            }
            toast('❌ ' + msg, 'error');
            btn.disabled = false;
            btn.textContent = orig;
        }
    } catch (err) {
        toast('خطأ: ' + err.message, 'error');
        btn.disabled = false;
        btn.textContent = orig;
    }
});

function escapeHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
</script>