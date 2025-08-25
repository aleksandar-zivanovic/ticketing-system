<?php if ($type !== "link"): ?>
    <button type="<?= $type ?>" id="<?= $name ?>" class="w-full py-3 px-4 text-sm tracking-wider font-bold rounded-md <?= $textColor ?> <?= $bgColor ?> <?= $hoverBgColor ?> <?= $otherClasses ?? "" ?> focus:outline-none" <?= $otherAttributes ?? "" ?>>
        <?= !empty($icon) ? "<span class='{$icon}'></span>" : "" ?> <?= $value ?>
    </button>
<?php else: ?>
    <a href="<?= $link ?>" id="<?= $name ?>" class="w-full block py-3 px-4 text-sm text-center tracking-wider font-bold rounded-md <?= $textColor ?> <?= $bgColor ?> <?= $hoverBgColor ?> <?= $otherClasses ?? "" ?> focus:outline-none" <?= !empty($icon) ? "<span class='{$icon}'></span>" : "" ?> <?= $otherAttributes ?? "" ?>>
        <?= $value ?>
    </a>
<?php endif; ?>