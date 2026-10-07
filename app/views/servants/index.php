<div class="page-head">
    <h1 class="page-title"><?= e(__('nav.servants')) ?></h1>
    <?php if (hasPermission('servants.create')): ?>
        <a href="<?= e(appBaseUrl()) ?>/servants/create" class="btn btn-primary">
            + <?= e(__('servants.add')) ?>
        </a>
    <?php endif; ?>
</div>

<form method="get" class="filters-bar">
    <label>
        <span>بحث</span>
        <input type="search" name="search" value="<?= e($filters['search'] ?? '') ?>"
               placeholder="<?= e(__('servants.search')) ?>">
    </label>
    <label>
        <span><?= e(__('servants.choir')) ?></span>
        <select name="choir_id">
            <option value="">الكل</option>
            <?php foreach ($choirs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ($filters['choir_id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        <span><?= e(__('servants.status')) ?></span>
        <select name="status">
            <option value="">الكل</option>
            <option value="active" <?= ($filters['status'] === 'active') ? 'selected' : '' ?>>نشط</option>
            <option value="inactive" <?= ($filters['status'] === 'inactive') ? 'selected' : '' ?>>غير نشط</option>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">تطبيق</button>
</form>

<?php if (empty($servants)): ?>
    <div class="empty-state">
        لا يوجد خدام بعد. 
        <?php if (hasPermission('servants.create')): ?>
            <br><br>
            <a href="<?= e(appBaseUrl()) ?>/servants/create" class="btn btn-primary">
                + <?= e(__('servants.add')) ?>
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>

    <div class="servant-card-grid stagger">
    <?php foreach ($servants as $s): ?>
        <a href="<?= e(appBaseUrl()) ?>/servants/show?id=<?= (int)$s['id'] ?>" class="servant-card">
            <div class="avatar-lg"><?= e($s['emoji'] ?? '👤') ?></div>
            <div class="info">
                <div class="name"><?= e($s['name']) ?></div>
                <div class="meta">
                    <?= e($s['choir_name']) ?>
                    <?php if ($s['code']): ?>
                        · <code><?= e($s['code']) ?></code>
                    <?php endif; ?>
                </div>
                <div class="meta" style="margin-top:6px">
                    <span class="badge <?= $s['status'] === 'active' ? 'badge-green' : 'badge-muted' ?>">
                        <?= e(servantStatusLabel($s['status'])) ?>
                    </span>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
    </div>

<?php endif; ?>