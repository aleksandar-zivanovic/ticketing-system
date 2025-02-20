<div class="navbar-item dropdown has-divider has-user-avatar">
    <a class="navbar-link">
        <div class="user-avatar">
            <img src="https://api.dicebear.com/9.x/pixel-art/svg" alt="@" class="rounded-full">
        </div>
        <div class="is-user-name"><span><?= $_SESSION['user_name'] . " " . $_SESSION['user_surname']?></span></div>
        <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
    </a>
    <div class="navbar-dropdown">
        <a href="profile.html" class="navbar-item">
            <span class="icon"><i class="mdi mdi-account"></i></span>
            <span>My Profile</span>
        </a>
        <a class="navbar-item">
            <span class="icon"><i class="mdi mdi-settings"></i></span>
            <span>Settings</span>
        </a>
        <a class="navbar-item">
            <span class="icon"><i class="mdi mdi-email"></i></span>
            <span>Messages</span>
        </a>
        <hr class="navbar-divider">
        <?php require_once '_logout_button.php' ?>
    </div>
</div>