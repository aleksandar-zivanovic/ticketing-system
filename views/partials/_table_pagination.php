<div class="table-pagination">
    <div class="flex items-center justify-between">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <form action="">
                <label for="results">Results per page: </label>
                <select name="limit" id="results" onchange="this.form.submit()">
                    <?php foreach ($options as $opt): ?>
                        <option value="<?= $opt['value'] ?>" <?= $opt['selected'] ? 'selected' : '' ?>>
                            <?= $opt['value'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($_GET['sort']) && !empty(trim($_GET['sort']))) : ?>
                    <input type="hidden" name="sort" value="<?php echo cleanString($_GET['sort']); ?>">
                <?php endif; ?>
            </form>
        </div>
        <div class="buttons">
            <?php
            foreach ($pages as $page):
                if ($page == $currentPage): ?>
                    <button type="button" class="button active"><?= $page ?></button>
                <?php else: ?>
                    <a href="<?= $pagination->generateUrl($page, $limit) ?>" class="button"><?= $page ?></a>
            <?php
                endif;
            endforeach;
            ?>
        </div>
        <small><?= "Page {$currentPage} of {$totalPages}" ?></small>
    </div>
</div>