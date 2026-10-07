<?php if (!empty($_SESSION['_success'])): ?>
    <div class="toast toast-success" data-auto-dismiss="4000">
        ✓ <?= e($_SESSION['_success']) ?>
    </div>
    <?php unset($_SESSION['_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['_errors'])): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ((array)$_SESSION['_errors'] as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>