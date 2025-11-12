<header class="card-header">
    <p class="card-header-title text-xl">
        User statistics:
    </p>
</header>
<div class="grid gap-6 grid-cols-1 md:grid-cols-<?= $rolesCount + 1 ?> m-6 mt-1">
    <?php

    // User statistics
    renderDashboardCard(
        label: "Total users",
        count: $totalUsers,
        iconColor: "text-purple-500",
        icon: "mdi-account-group"
    );

    foreach ($usersAndRolesCount as $roleName => $userCount) {
        $iconClass = match ($roleName) {
            "admin"     => "mdi-shield-account",
            "moderator" => "mdi-account-tie",
            "user"      => "mdi-account-check",
            "blocked"   => "mdi-account-cancel",
            default     => "mdi-account-question"
        };

        renderDashboardCard(
            label: ucfirst($roleName) . "s",
            count: $userCount,
            iconColor: "text-purple-500",
            icon: $iconClass
        );
    }
    ?>
</div>