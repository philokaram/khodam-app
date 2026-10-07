<?php
// مصفوفة إيموجي متاحة
$emojiOptions = ['😀', '😎', '🥳', '🤓', '😇', '🙂', '😄', '😊', '😁', '🤗', '🙌', '👨‍🎓', '👨‍💼', '👨‍🏫', '🧑', '👤'];
?>
<div class="page-head">
    <h1 class="page-title"><?= e(__('nav.servants')) ?></h1>
    <?php if (hasPermission('servants.create')): ?>
        <a href="<?= e(config('app.url')) ?>/servants/create" class="btn btn-primary">+ <?= e(__('servants.add')) ?></a>
    <?php endif; ?>
</div>

<form method="get" class="filters-bar">
    <label>
        <span>بحث</span>
        <input type="search" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="<?= e(__('servants.search')) ?>">
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
            <option value="active"   <?= ($filters['status'] === 'active')   ? 'selected' : '' ?>><?= e(__('status.active')) ?></option>
            <option value="inactive" <?= ($filters['status'] === 'inactive') ? 'selected' : '' ?>><?= e(__('status.inactive')) ?></option>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">تطبيق</button>
</form>

<?php if (empty($servants)): ?>
    <div class="empty-state"><?= e(__('messages.no_data')) ?></div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th><?= e(__('servants.name')) ?></th>
            <th><?= e(__('servants.choir')) ?></th>
            <th><?= e(__('servants.code')) ?></th>
            <th><?= e(__('servants.phone')) ?></th>
            <th><?= e(__('servants.status')) ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($servants as $s): ?>
        <tr>
            <td><?= e($s['name']) ?></td>
            <td><?= e($s['choir_name']) ?></td>
            <td><?= e($s['code'] ?? '—') ?></td>
            <td><?= e($s['phone'] ?? '—') ?></td>
            <td>
                <span class="badge <?= $s['status'] === 'active' ? 'badge-green' : 'badge-muted' ?>">
                    <?= e(servantStatusLabel($s['status'])) ?>
                </span>
            </td>
            <td>
                <a href="<?= e(config('app.url')) ?>/servants/show?id=<?= (int)$s['id'] ?>" class="btn btn-sm">عرض</a>
                <?php if (hasPermission('servants.edit')): ?>
                    <a href="<?= e(config('app.url')) ?>/servants/edit?id=<?= (int)$s['id'] ?>" class="btn btn-sm">تعديل</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>