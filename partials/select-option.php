<div class="text-gray-800 text-sm mb-2 block w-full">
    <label for="<?= $name ?> class="w-full block py-2"><?= $label ?></label>
    <select name="<?= $name ?>" id="<?= $name ?>" class="w-full block p-2">
        <?php
            foreach ($data as $key) {
                echo $key['name'] . "<br>";
                echo "<option value='{$key['id']}'>{$key['name']}</option><br>";
            }
        ?>
    </select>
</div>