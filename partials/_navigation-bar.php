<nav id="navbar-main" class="navbar is-fixed-top">

  <!-- search bar -->
  <?php // require_once '_navigation-search.php'; ?>
  
  <div class="navbar-brand is-right">
    <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
      <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
    </a>
  </div>
  <div class="navbar-menu" id="navbar-menu">
    <div class="navbar-end">
      <!-- profile menu -->
      <?php require_once '_navigation-profile-menu.php'; ?>

      <!-- navigation buttons -->
      <?php require_once '_navigation-buttons.php'; ?>
    </div>
  </div>
</nav>