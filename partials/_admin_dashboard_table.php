<div>
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
            <?= $category ?>
        </p>
        <a href="#" class="card-header-icon">
            <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
    </header>
    <div class="card-content border-2 border-black">
        <table>
            <thead>
                <tr>
                    <th><?= ucfirst($columnName) ?>:</th>
                    <th>Total:</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($items as [$label, $count]) {
                    echo "<tr>";
                    echo "<td>{$label}</td>";
                    echo "<td>{$count}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>