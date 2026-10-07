<?php
$variant = $variant ?? 'primary';
$type    = $type ?? 'button';
$text    = $text ?? '';
$id      = $id ?? '';
$attrs   = $attrs ?? '';
?>
<button type="<?= e($type) ?>" class="btn btn-<?= e($variant) ?>" <?= $id ? 'id="'.e($id).'"' : '' ?> <?= $attrs ?>>
    <?= e($text) ?>
</button>