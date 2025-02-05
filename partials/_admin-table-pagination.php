<div class="table-pagination">
    <div class="flex items-center justify-between">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <form action="">
                <label for="results">Results per page: </label>
                <select name="limit" id="results" onchange="this.form.submit()">
                    <option value="5" <?php echo addSelectedTag("limit", 5, true); ?>>5</option>
                    <option value="10" <?php 
                    echo addSelectedTag("limit", 10); 
                    if (
                        (!isset($_GET["limit"]) && !isset($_SESSION["limit"])) || 
                        (isset($_GET["limit"]) && $_GET["limit"] == 10) || 
                        (isset($_SESSION["limit"]) && $_SESSION["limit"] == 10)
                        ) 
                    {
                        echo "selected";
                    }
                    ?>>10</option>
                    <option value="20" <?php echo addSelectedTag("limit", 20, true); ?>>20</option>
                    <option value="50" <?php echo addSelectedTag("limit", 50, true); ?>>50</option>
                    <option value="all" <?php echo addSelectedTag("limit", "all", true); ?>>All</option>
                </select>
            </form>
        </div>
        <div class="buttons">
            <?php
            for ($i = $currentPage - 2; $i < $currentPage; $i++) { 
                if ($i > 0): 
            ?>
                <a href="<?= $pagination->generateUrl($i) ?>" class="button"><?= $i ?></a>
            <?php
                endif;
            }
            ?>
            <button type="button" class="button active"><?= $currentPage?></button>
            <?php
            for ($i = $currentPage + 1; $i <= $currentPage + 2; $i++): 
                if ($i <= $totalPages): 
            ?>
                <a href="<?= $pagination->generateUrl($i) ?>" class="button"><?= $i ?></a>
            <?php
                endif;
            endfor;
            ?>
        </div>
        <small><?="Page {$currentPage} of {$totalPages}"?></small>
    </div>
</div>