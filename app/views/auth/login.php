<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">⛪</div>
            <h1><?= e(__('app.name')) ?></h1>
            <p class="auth-subtitle"><?= e(__('auth.login_title')) ?></p>
        </div>

        <form method="post" action="<?= e(appBaseUrl()) ?>/login" class="form" autocomplete="on">
            <?= csrfField() ?>

            <label>
                <span><?= e(__('auth.username')) ?></span>
                <input
                    type="text"
                    name="username"
                    value="<?= e($_SESSION['_old']['username'] ?? '') ?>"
                    required
                    autofocus
                    autocomplete="username"
                    dir="ltr"
                    placeholder="admin">
            </label>

            <label>
                <span><?= e(__('auth.password')) ?></span>
                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    dir="ltr"
                    placeholder="••••••••">
            </label>

            <button type="submit" class="btn btn-primary btn-lg">
                <?= e(__('auth.login')) ?>
            </button>
        </form>

        <p class="auth-footer">
            © <?= date('Y') ?> <?= e(__('app.name')) ?>
        </p>
    </div>
</div>
<?php unset($_SESSION['_old']); ?>