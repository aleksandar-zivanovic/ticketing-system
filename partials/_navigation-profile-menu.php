<div class="navbar-item dropdown has-divider has-user-avatar">
    <a class="navbar-link">
        <div class="user-avatar">
            <img src="https://api.dicebear.com/9.x/pixel-art/svg" alt="@" class="rounded-full">
        </div>
        <div class="is-user-name"><span><?= $_SESSION['user_name'] . " " . $_SESSION['user_surname']?></span></div>
        <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
    </a>
    <div class="navbar-dropdown">
        <a href="/ticketing-system/public/profile.php?user=<?= cleanString($_SESSION["user_id"]) ?>" class="navbar-item">
            <span class="icon"><i class="mdi mdi-account"></i></span>
            <span>My Profile</span>
        </a>
        <hr class="navbar-divider">
        <?php require_once '_logout_button.php' ?>
    </div>
</div>