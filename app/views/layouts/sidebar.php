<?php
$base = appBaseUrl();
$current = $_SERVER['REQUEST_URI'] ?? '';
$is = fn(string $p) => str_contains($current, $p);
?>
<aside class="app-sidebar">
    <nav>
        <a href="<?= e($base) ?>/dashboard" class="<?= $is('/dashboard') ? 'on' : '' ?>">
            <?= e(__('nav.dashboard')) ?>
        </a>
        <a href="<?= e($base) ?>/servants" class="<?= $is('/servants') ? 'on' : '' ?>">
            <?= e(__('nav.servants')) ?>
        </a>
        <a href="<?= e($base) ?>/choirs" class="<?= $is('/choirs') ? 'on' : '' ?>">
            <?= e(__('nav.choirs')) ?>
        </a>
        <a href="<?= e($base) ?>/activities" class="<?= $is('/activities') ? 'on' : '' ?>">
            <?= e(__('nav.activities')) ?>
        </a>
        <a href="<?= e($base) ?>/attendance/create" class="<?= $is('/attendance/create') ? 'on' : '' ?>">
            <?= e(__('nav.new_attendance')) ?>
        </a>
        <a href="<?= e($base) ?>/attendance/history" class="<?= $is('/attendance/history') ? 'on' : '' ?>">
            <?= e(__('nav.history')) ?>
        </a>
        <a href="<?= e($base) ?>/reports" class="<?= $is('/reports') ? 'on' : '' ?>">
            <?= e(__('nav.reports')) ?>
        </a>
        <a href="<?= e($base) ?>/users" class="<?= $is('/users') ? 'on' : '' ?>">
            <?= e(__('nav.users')) ?>
        </a>
    </nav>
</aside>