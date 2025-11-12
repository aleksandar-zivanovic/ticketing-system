<div>
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
            <?= $category ?>
        </p>
    </header>
    <div class="card-content border-2 border-black shadow-lg shadow-blue-500/50">
        <table class="table-fixed w-full">
            <thead>
                <tr>
                    <th class="w-1/2 bg-blue-100"><?= ucfirst($columnName) ?>:</th>
                    <th class="w-1/4 bg-blue-100 text-center">Total:</th>
                    <th class="w-1/4 bg-blue-100 text-center">%</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($items as [$label, $count, $percentage]) {
                    echo "<tr>";
                    echo "<td class='w-full lg:w-1/2 text-center lg:text-left'><div class='w-full'>{$label}</div></td>";
                    echo "<td class='w-full lg:w-1/4 text-center'><div class='w-full'>{$count}</div>
                    </td>";
                    echo "<td class='w-full lg:w-1/4 text-center'><div class='w-full'>{$percentage}%</div></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>