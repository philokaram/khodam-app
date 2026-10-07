<?php
$me = currentUser();
$roleName = $me['role_name'] ?? '';
$roleLabel = $me['role_label'] ?? $roleName;

// تحديد الخطوات حسب الدور
if (in_array($roleName, ['SUPER_ADMIN', 'ADMIN'], true)) {
    $steps = [
        [
            'emoji' => '👋',
            'color' => 'primary',
            'title' => 'أهلاً بك في نظام حضور الخدام',
            'desc' => 'منصة متكاملة لمتابعة حضور الخدام في جميع الأنشطة والاجتماعات.',
            'highlight' => 'يمكنك إدارة كل شيء من مكان واحد',
        ],
        [
            'emoji' => '🎯',
            'color' => 'info',
            'title' => 'لوحة التحكم',
            'desc' => 'نظرة سريعة على كل الإحصائيات والبيانات. تعرض لك عدد الخدام، الحضور، الغياب، والنسب.',
            'highlight' => 'كل الأرقام في مكان واحد',
        ],
        [
            'emoji' => '👥',
            'color' => 'success',
            'title' => 'إدارة الخدام',
            'desc' => 'أضف خداماً جدداً، عدّل بياناتهم، استورد من Excel، صدّر التقارير، وكل خادم يحصل على حساب دخول.',
            'highlight' => 'كل خادم = مستخدم بحساب خاص',
        ],
        [
            'emoji' => '✅',
            'color' => 'warning',
            'title' => 'تسجيل الحضور',
            'desc' => 'سجّل الحضور في الاجتماعات بسرعة: اختر النشاط والخورس والتاريخ، حدّد الحاضرين، واحفظ بنقرة.',
            'highlight' => 'الكل حاضر بضغطة واحدة',
        ],
        [
            'emoji' => '📊',
            'color' => 'purple',
            'title' => 'التقارير والإحصائيات',
            'desc' => 'تقارير مفصلة لكل خادم ونشاط. صدّرها إلى Excel لمشاركتها مع الإدارة.',
            'highlight' => 'تقارير احترافية بنقرة',
        ],
        [
            'emoji' => '🚀',
            'color' => 'primary',
            'title' => 'أنت جاهز الآن!',
            'desc' => 'كل شيء جاهز. ابدأ بتسجيل حضور أول اجتماع، أو أضف خداماً جدداً.',
            'highlight' => 'بالتوفيق في خدمتك',
        ],
    ];
} elseif ($roleName === 'CHOIR_ADMIN') {
    $steps = [
        [
            'emoji' => '👋',
            'color' => 'primary',
            'title' => 'أهلاً بك',
            'desc' => 'أنت مسؤول خورس ' . e($me['choir_name'] ?? '') . ' في نظام حضور الخدام.',
            'highlight' => 'لديك وصول لخورسك فقط',
        ],
        [
            'emoji' => '📊',
            'color' => 'info',
            'title' => 'لوحة التحكم',
            'desc' => 'إحصائيات خورسك فقط: عدد الخدام، الحضور، الغياب، والنسب.',
            'highlight' => 'كل الأرقام لخورسك',
        ],
        [
            'emoji' => '👥',
            'color' => 'success',
            'title' => 'خدام خورسك',
            'desc' => 'استعرض بيانات خدامك، ابحث عنهم، وفلترهم بحرية.',
            'highlight' => 'خدامك في مكان واحد',
        ],
        [
            'emoji' => '📜',
            'color' => 'warning',
            'title' => 'سجل الحضور',
            'desc' => 'راجع كل جلسات حضور خورسك السابقة، واعرف من حضر ومن غاب.',
            'highlight' => 'تاريخ كامل لخورسك',
        ],
        [
            'emoji' => '📈',
            'color' => 'purple',
            'title' => 'التقارير',
            'desc' => 'تقارير مفصلة لخدامك ونسب حضورهم. صدّرها إلى Excel.',
            'highlight' => 'تقارير خورسك',
        ],
        [
            'emoji' => '🚀',
            'color' => 'primary',
            'title' => 'أنت جاهز!',
            'desc' => 'ابدأ باستعراض إحصائيات خورسك، أو راجع سجل الحضور.',
            'highlight' => 'بالتوفيق',
        ],
    ];
} elseif ($roleName === 'ATTENDANCE_USER') {
    $steps = [
        [
            'emoji' => '👋',
            'color' => 'primary',
            'title' => 'أهلاً بك',
            'desc' => 'أنت مسؤول تسجيل الحضور في نظام حضور الخدام.',
            'highlight' => 'يمكنك تسجيل الحضور لأي خورس',
        ],
        [
            'emoji' => '✅',
            'color' => 'success',
            'title' => 'تسجيل الحضور',
            'desc' => 'اختر النشاط والخورس والتاريخ، ثم سجّل الحضور بسرعة.',
            'highlight' => 'الكل حاضر بضغطة',
        ],
        [
            'emoji' => '📜',
            'color' => 'info',
            'title' => 'سجل الحضور',
            'desc' => 'راجع كل الجلسات السابقة. فلتر بالتاريخ والنشاط والخورس.',
            'highlight' => 'تاريخ كامل',
        ],
        [
            'emoji' => '📊',
            'color' => 'purple',
            'title' => 'التقارير',
            'desc' => 'اعرض إحصائيات الحضور لكل خادم ونشاط، وصدّرها إلى Excel.',
            'highlight' => 'تقارير شاملة',
        ],
        [
            'emoji' => '🚀',
            'color' => 'primary',
            'title' => 'أنت جاهز!',
            'desc' => 'ابدأ بتسجيل حضور أول اجتماع.',
            'highlight' => 'بالتوفيق',
        ],
    ];
} else { // SERVANT
    $steps = [
        [
            'emoji' => '👋',
            'color' => 'primary',
            'title' => 'أهلاً بك',
            'desc' => 'أنت الآن في نظام حضور الخدام. يمكنك متابعة حضورك في كل الأنشطة.',
            'highlight' => 'حسابك جاهز',
        ],
        [
            'emoji' => '👤',
            'color' => 'info',
            'title' => 'ملفي الشخصي',
            'desc' => 'اعرض بياناتك: الخورس، الكود، الهاتف، وكل معلوماتك. يمكنك تغيير كلمة المرور من هناك.',
            'highlight' => 'بياناتك الكاملة',
        ],
        [
            'emoji' => '📜',
            'color' => 'success',
            'title' => 'سجل حضوري',
            'desc' => 'شاهد كل سجلات حضورك السابقة: متى حضرت، متى غبت، ومتى كان لديك عذر.',
            'highlight' => 'تاريخك الكامل',
        ],
        [
            'emoji' => '📊',
            'color' => 'warning',
            'title' => 'تقريري الشخصي',
            'desc' => 'إحصائياتك الخاصة: عدد الجلسات، نسبة الحضور، وتوزيع حضورك حسب النشاط.',
            'highlight' => 'اعرف مستواك',
        ],
        [
            'emoji' => '🚀',
            'color' => 'primary',
            'title' => 'أنت جاهز!',
            'desc' => 'ابدأ باستعراض ملفك الشخصي وسجل حضورك.',
            'highlight' => 'بالتوفيق في خدمتك',
        ],
    ];
}
?>

<!-- Onboarding Modal -->
<div id="onboardingModal" class="onboarding-backdrop" hidden>
    <div class="onboarding-container">
        <div class="onboarding-progress-top" id="onboardingProgressTop">
            <?php foreach ($steps as $i => $s): ?>
                <div class="onboarding-progress-bar" data-step="<?= $i ?>"></div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="onboarding-skip" id="onboardingSkipBtn" title="تخطي الجولة">
            ✕
        </button>

        <div class="onboarding-content" id="onboardingContent">
            <?php foreach ($steps as $i => $s): ?>
                <div class="onboarding-step <?= $i === 0 ? 'active' : '' ?>" data-step="<?= $i ?>">
                    <div class="onboarding-emoji-wrap color-<?= e($s['color']) ?>">
                        <div class="onboarding-emoji"><?= $s['emoji'] ?></div>
                        <div class="onboarding-emoji-glow"></div>
                    </div>

                    <h2 class="onboarding-title"><?= $s['title'] ?></h2>
                    <p class="onboarding-desc"><?= $s['desc'] ?></p>

                    <div class="onboarding-highlight color-<?= e($s['color']) ?>">
                        ✨ <?= $s['highlight'] ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="onboarding-dots" id="onboardingDots">
            <?php foreach ($steps as $i => $s): ?>
                <button type="button" class="onboarding-dot <?= $i === 0 ? 'active' : '' ?>" data-step="<?= $i ?>" aria-label="خطوة <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="onboarding-actions">
            <button type="button" class="onboarding-btn onboarding-btn-prev" id="onboardingPrev" disabled>
                ← السابق
            </button>
            <button type="button" class="onboarding-btn onboarding-btn-next" id="onboardingNext">
                التالي →
            </button>
        </div>

        <div class="onboarding-footer">
            <button type="button" class="onboarding-btn-skip" id="onboardingSkipText">
                تخطي الجولة
            </button>
        </div>
    </div>
</div>

<style>
/* ============================================================
   ONBOARDING — Modal احترافي
   ============================================================ */
.onboarding-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9999;
    display: grid;
    place-items: center;
    padding: 20px;
    animation: onboardFadeIn 0.3s ease-out;
}

.onboarding-backdrop[hidden] { display: none; }

@keyframes onboardFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.onboarding-container {
    position: relative;
    background: var(--bg-surface);
    border-radius: 28px;
    width: 100%;
    max-width: 520px;
    padding: 48px 40px 32px;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.3),
                0 8px 24px rgba(0, 0, 0, 0.15);
    animation: onboardSlideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    overflow: hidden;
}

@keyframes onboardSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ============ Progress Bar Top ============ */
.onboarding-progress-top {
    position: absolute;
    top: 0;
    inset-inline: 0;
    height: 4px;
    display: flex;
    gap: 3px;
    padding: 0 4px;
}

.onboarding-progress-bar {
    flex: 1;
    height: 100%;
    background: var(--bg-subtle);
    border-radius: 0 0 4px 4px;
    overflow: hidden;
    position: relative;
    transition: background 0.3s;
}

.onboarding-progress-bar::after {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--primary);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.onboarding-progress-bar.done::after {
    transform: scaleX(1);
}

.onboarding-progress-bar.active::after {
    transform: scaleX(1);
    background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
}

/* ============ Skip Button (X) ============ */
.onboarding-skip {
    position: absolute;
    top: 16px;
    inset-inline-end: 16px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--bg-subtle);
    border: none;
    color: var(--text-muted);
    font-size: 16px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.15s;
    z-index: 10;
}

.onboarding-skip:hover {
    background: var(--danger);
    color: #fff;
    transform: rotate(90deg);
}

/* ============ Content ============ */
.onboarding-content {
    position: relative;
    min-height: 340px;
}

.onboarding-step {
    display: none;
    text-align: center;
    animation: onboardStepIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.onboarding-step.active {
    display: block;
}

@keyframes onboardStepIn {
    from { opacity: 0; transform: translateX(-20px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ============ Emoji ============ */
.onboarding-emoji-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 28px;
    display: grid;
    place-items: center;
    border-radius: 32px;
    animation: onboardEmojiFloat 3s ease-in-out infinite;
}

@keyframes onboardEmojiFloat {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-8px); }
}

.onboarding-emoji {
    font-size: 64px;
    position: relative;
    z-index: 2;
    filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.15));
}

.onboarding-emoji-glow {
    position: absolute;
    inset: 0;
    border-radius: 32px;
    filter: blur(24px);
    opacity: 0.5;
    z-index: 1;
    animation: onboardGlow 3s ease-in-out infinite;
}

@keyframes onboardGlow {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50%      { transform: scale(1.15); opacity: 0.7; }
}

/* ألوان */
.onboarding-emoji-wrap.color-primary { background: linear-gradient(135deg, rgba(15, 118, 110, 0.15) 0%, rgba(20, 184, 166, 0.15) 100%); }
.onboarding-emoji-wrap.color-primary .onboarding-emoji-glow { background: radial-gradient(circle, var(--primary) 0%, transparent 70%); }

.onboarding-emoji-wrap.color-info { background: linear-gradient(135deg, rgba(2, 132, 199, 0.15) 0%, rgba(56, 189, 248, 0.15) 100%); }
.onboarding-emoji-wrap.color-info .onboarding-emoji-glow { background: radial-gradient(circle, var(--info) 0%, transparent 70%); }

.onboarding-emoji-wrap.color-success { background: linear-gradient(135deg, rgba(22, 163, 74, 0.15) 0%, rgba(34, 197, 94, 0.15) 100%); }
.onboarding-emoji-wrap.color-success .onboarding-emoji-glow { background: radial-gradient(circle, var(--success) 0%, transparent 70%); }

.onboarding-emoji-wrap.color-warning { background: linear-gradient(135deg, rgba(217, 119, 6, 0.15) 0%, rgba(251, 191, 36, 0.15) 100%); }
.onboarding-emoji-wrap.color-warning .onboarding-emoji-glow { background: radial-gradient(circle, var(--warning) 0%, transparent 70%); }

.onboarding-emoji-wrap.color-purple { background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(167, 139, 250, 0.15) 100%); }
.onboarding-emoji-wrap.color-purple .onboarding-emoji-glow { background: radial-gradient(circle, #8B5CF6 0%, transparent 70%); }

/* ============ Title ============ */
.onboarding-title {
    font-size: 26px;
    font-weight: 900;
    color: var(--text-primary);
    margin: 0 0 14px;
    letter-spacing: -0.5px;
    line-height: 1.3;
}

/* ============ Description ============ */
.onboarding-desc {
    font-size: 15px;
    color: var(--text-secondary);
    line-height: 1.7;
    margin: 0 0 20px;
    max-width: 400px;
    margin-inline: auto;
}

/* ============ Highlight ============ */
.onboarding-highlight {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 99px;
    font-size: 13px;
    font-weight: 800;
    margin-top: 4px;
    animation: onboardPill 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes onboardPill {
    from { opacity: 0; transform: scale(0.8); }
    to   { opacity: 1; transform: scale(1); }
}

.onboarding-highlight.color-primary { background: rgba(15, 118, 110, 0.12); color: var(--primary); }
.onboarding-highlight.color-info    { background: rgba(2, 132, 199, 0.12);  color: var(--info); }
.onboarding-highlight.color-success { background: rgba(22, 163, 74, 0.12);  color: var(--success); }
.onboarding-highlight.color-warning { background: rgba(217, 119, 6, 0.12);  color: var(--warning); }
.onboarding-highlight.color-purple  { background: rgba(139, 92, 246, 0.12); color: #8B5CF6; }

/* ============ Dots ============ */
.onboarding-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 32px 0 24px;
}

.onboarding-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--bg-active);
    border: none;
    cursor: pointer;
    padding: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.onboarding-dot:hover {
    background: var(--text-muted);
}

.onboarding-dot.active {
    width: 32px;
    border-radius: 4px;
    background: var(--primary);
}

/* ============ Actions ============ */
.onboarding-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-bottom: 12px;
}

.onboarding-btn {
    padding: 14px 32px;
    border-radius: 14px;
    border: none;
    font-family: inherit;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.15s;
    min-width: 140px;
}

.onboarding-btn-prev {
    background: var(--bg-subtle);
    color: var(--text-secondary);
}

.onboarding-btn-prev:not(:disabled):hover {
    background: var(--bg-active);
    color: var(--text-primary);
}

.onboarding-btn-prev:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.onboarding-btn-next {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    box-shadow: 0 4px 16px color-mix(in srgb, var(--primary) 40%, transparent);
}

.onboarding-btn-next:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px color-mix(in srgb, var(--primary) 50%, transparent);
}

.onboarding-btn-next:active {
    transform: translateY(0);
}

.onboarding-btn-next.finish {
    background: linear-gradient(135deg, var(--success) 0%, #16A34A 100%);
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.4);
}

/* ============ Skip Text ============ */
.onboarding-footer {
    text-align: center;
}

.onboarding-btn-skip {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.15s;
}

.onboarding-btn-skip:hover {
    color: var(--text-primary);
    background: var(--bg-subtle);
}

/* ============ Responsive ============ */
@media (max-width: 560px) {
    .onboarding-container {
        padding: 40px 24px 24px;
        border-radius: 24px;
    }

    .onboarding-emoji-wrap {
        width: 100px;
        height: 100px;
    }

    .onboarding-emoji { font-size: 52px; }
    .onboarding-title { font-size: 22px; }
    .onboarding-desc { font-size: 14px; }
    .onboarding-content { min-height: 320px; }
    .onboarding-btn {
        padding: 12px 24px;
        min-width: 120px;
        font-size: 14px;
    }
}
</style>

<script>
(function initOnboarding() {
    var modal = document.getElementById('onboardingModal');
    if (!modal) return;

    // إذا رأى الجولة من قبل
    if (<?= (int)($me['onboarding_step'] ?? 0) ?> >= 99) {
        modal.remove();
        return;
    }

    modal.hidden = false;

    var currentStep = 0;
    var totalSteps = <?= count($steps) ?>;
    var steps = modal.querySelectorAll('.onboarding-step');
    var dots = modal.querySelectorAll('.onboarding-dot');
    var progressBars = modal.querySelectorAll('.onboarding-progress-bar');
    var btnPrev = document.getElementById('onboardingPrev');
    var btnNext = document.getElementById('onboardingNext');
    var btnSkip = document.getElementById('onboardingSkipBtn');
    var btnSkipText = document.getElementById('onboardingSkipText');

    function goToStep(index) {
        if (index < 0 || index >= totalSteps) return;

        // إخفاء الكل
        steps.forEach(function(s) { s.classList.remove('active'); });
        dots.forEach(function(d) { d.classList.remove('active'); });
        progressBars.forEach(function(b, i) {
            b.classList.remove('active');
            if (i < index) b.classList.add('done');
        });

        // إظهار الحالي
        steps[index].classList.add('active');
        dots[index].classList.add('active');
        progressBars[index].classList.add('active');

        currentStep = index;

        // أزرار
        btnPrev.disabled = (index === 0);

        if (index === totalSteps - 1) {
            btnNext.textContent = '🎉 ابدأ الآن';
            btnNext.classList.add('finish');
        } else {
            btnNext.textContent = 'التالي →';
            btnNext.classList.remove('finish');
        }
    }

    btnNext.addEventListener('click', function() {
        if (currentStep === totalSteps - 1) {
            finishOnboarding();
        } else {
            goToStep(currentStep + 1);
        }
    });

    btnPrev.addEventListener('click', function() {
        goToStep(currentStep - 1);
    });

    dots.forEach(function(dot, i) {
        dot.addEventListener('click', function() { goToStep(i); });
    });

    function finishOnboarding() {
        modal.hidden = true;

        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        fetch('/api/users/onboarding-done', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrf
            },
            body: JSON.stringify({})
        })
        .then(function(r) { return r.json(); })
        .then(function() {
            if (typeof toast === 'function') {
                toast('🎉 بالتوفيق في خدمتك', 'success');
            }
            modal.remove();
        })
        .catch(function() {
            modal.remove();
        });
    }

    btnSkip.addEventListener('click', finishOnboarding);
    btnSkipText.addEventListener('click', finishOnboarding);

    // Keyboard
    document.addEventListener('keydown', function(e) {
        if (modal.hidden) return;
        if (e.key === 'ArrowLeft')  goToStep(currentStep + 1); // RTL
        if (e.key === 'ArrowRight') goToStep(currentStep - 1); // RTL
        if (e.key === 'Escape')     finishOnboarding();
    });

    // Swipe (للموبايل)
    var touchStartX = 0;
    var touchStartY = 0;
    modal.addEventListener('touchstart', function(e) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    modal.addEventListener('touchend', function(e) {
        var dx = e.changedTouches[0].clientX - touchStartX;
        var dy = e.changedTouches[0].clientY - touchStartY;

        // تجاهل السحب العمودي
        if (Math.abs(dy) > Math.abs(dx)) return;
        if (Math.abs(dx) < 50) return;

        // RTL: سحب لليمين = التالي
        if (dx > 50) goToStep(currentStep + 1);
        else         goToStep(currentStep - 1);
    }, { passive: true });

    // ابدأ من الخطوة 0
    goToStep(0);
})();
</script>