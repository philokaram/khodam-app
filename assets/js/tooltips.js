'use strict';

/* ============================================================
   Tooltips System
============================================================ */
(function() {
    var tooltipEl = null;
    var currentTarget = null;

    function createTooltipEl() {
        var el = document.createElement('div');
        el.id = 'appTooltip';
        el.style.cssText = `
            position: fixed;
            z-index: 9999;
            background: var(--ink);
            color: var(--bg-surface);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            max-width: 280px;
            line-height: 1.5;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 0.15s ease, transform 0.15s ease;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            font-family: inherit;
        `;
        document.body.appendChild(el);
        return el;
    }

    function showTooltip(target, text, placement) {
        if (!tooltipEl) tooltipEl = createTooltipEl();

        tooltipEl.textContent = text;
        tooltipEl.style.opacity = '1';
        tooltipEl.style.transform = 'translateY(0)';

        var rect = target.getBoundingClientRect();
        var tooltipRect = tooltipEl.getBoundingClientRect();

        var top, left;

        if (placement === 'bottom') {
            top = rect.bottom + 8;
            left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        } else if (placement === 'left') {
            top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
            left = rect.left - tooltipRect.width - 8;
        } else if (placement === 'right') {
            top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
            left = rect.right + 8;
        } else {
            // top (default)
            top = rect.top - tooltipRect.height - 8;
            left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        }

        // حدود الشاشة
        if (left < 8) left = 8;
        if (left + tooltipRect.width > window.innerWidth - 8) {
            left = window.innerWidth - tooltipRect.width - 8;
        }
        if (top < 8) {
            top = rect.bottom + 8;
        }

        tooltipEl.style.top = top + 'px';
        tooltipEl.style.left = left + 'px';
    }

    function hideTooltip() {
        if (tooltipEl) {
            tooltipEl.style.opacity = '0';
            tooltipEl.style.transform = 'translateY(-4px)';
        }
        currentTarget = null;
    }

    // مراقبة العناصر التي لها data-tooltip
    document.addEventListener('mouseover', function(e) {
        var target = e.target.closest('[data-tooltip]');
        if (target && target !== currentTarget) {
            currentTarget = target;
            var text = target.getAttribute('data-tooltip');
            var placement = target.getAttribute('data-tooltip-placement') || 'top';
            if (text) showTooltip(target, text, placement);
        }
    });

    document.addEventListener('mouseout', function(e) {
        var target = e.target.closest('[data-tooltip]');
        if (target) {
            hideTooltip();
        }
    });

    // إخفاء عند التمرير
    window.addEventListener('scroll', hideTooltip, { passive: true });
})();