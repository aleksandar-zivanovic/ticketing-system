<input id="<?= $id ?>" name="<?= $name ?>" type="checkbox" class="h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
<label for="<?= $id ?>" class="text-gray-800 ml-3 block text-sm">
<?php echo $agreeText;
if ($agreeUrl): ?> 
    <a href="<?= $agreeUrl ?>" target="_blank" class="text-blue-600 font-semibold hover:underline ml-1"><?= $agreeUrlDescription ?></a>
<?php endif;?>
</label>