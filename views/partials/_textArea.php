<div>
    <?php if ($label != null): ?>
    <label class="text-gray-800 text-sm mb-2 block"><?= $label ?></label>
    <?php endif ?>

    <textarea name="<?= $name ?>" rows="8" class="border border-black w-full"><?php persist_input($name) ?></textarea>
</div>