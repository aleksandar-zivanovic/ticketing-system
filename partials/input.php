<?php if ($type !== "hidden") echo "<div>"; ?>

<?php if ($label != null): ?>
    <label class="text-gray-800 text-sm mb-2 block"><?= $label ?></label>
<?php endif ?>
    
<input 
    name="<?= $name ?>" 
    type="<?= $type ?>" 

    <?php 
    // Attributes for non file data
    if ($type != "file") {       
        if (empty($value)): ?>
            value="<?php persist_input($name) ?>" 
        <?php else: ?>
            value="<?php echo $value ?>" 
        <?php endif; ?>

        <?php if (!empty($placeholder) && empty($value)): ?> 
            placeholder="<?= $placeholder ?>" 
        <?php endif; ?>
    <?php 
    } else { 
        // Attributes for file data
    ?>
        multiple
    <?php
    } // Ends "if ($type != 'file')" block including the else part
    ?>

    <?php
        $class = 'class="bg-gray-100 w-full text-gray-800 text-sm px-4 py-3.5 rounded-md focus:bg-transparent outline-blue-500 transition-all"';
        if ($type !== "hidden") echo $class;
    ?>

/>

<?php if ($type !== "hidden") echo "</div>"; ?>