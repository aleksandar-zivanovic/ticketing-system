<div>
    <label class="text-gray-800 text-sm mb-2 block"><?= $label ?></label>
    <input name="<?= $name ?>" type="<?= $type ?>" class="bg-gray-100 w-full text-gray-800 text-sm px-4 py-3.5 rounded-md focus:bg-transparent outline-blue-500 transition-all" value="<?php persist_input($name) ?>" placeholder="<?= $placeholder ?>" />
</div>