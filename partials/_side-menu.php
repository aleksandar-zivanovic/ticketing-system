<aside class="aside is-placed-left is-expanded">
  <div class="aside-tools">
    <div>
      Admin <b class="font-black">One</b>
    </div>
  </div>
  <div class="menu is-menu-main">
    <p class="menu-label">General</p>
    <ul class="menu-list">
      <?php if ($_SESSION['user_role'] === "admin") : ?>
        <li class="--set-active-tables-html">
          <a href="/ticketing-system/public/admin/dashboard.php">
            <span class="icon"><i class="mdi mdi-view-dashboard-edit"></i></span>
            <span class="menu-item-label">Dashboard</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="--set-active-tables-html">
        <a href="/ticketing-system/public/user/my-dashboard.php">
          <span class="icon"><i class="mdi mdi-view-dashboard"></i></span>
          <span class="menu-item-label">My Dashboard</span>
        </a>
      </li>
    </ul>
    <p class="menu-label">Menu</p>
    <ul class="menu-list">
      <?php if ($_SESSION['user_role'] === "admin") : ?>
        <li class="--set-active-tables-html">
          <a href="/ticketing-system/public/admin/admin-ticket-listing.php">
            <span class="icon"><i class="mdi mdi-ticket-confirmation"></i></span>
            <span class="menu-item-label">Tickets</span>
          </a>
        </li>
      <?php endif; ?>

      <li class="--set-active-tables-html">
        <a href="/ticketing-system/public/user/user-ticket-listing.php">
          <span class="icon"><i class="mdi mdi-ticket-account"></i></span>
          <span class="menu-item-label">My Tickets</span>
        </a>
      </li>

      <?php if ($_SESSION['user_role'] === "admin") : ?>
        <li class="--set-active-tables-html">
          <a href="admin-tickets-i-handle.php">
            <span class="icon"><i class="mdi mdi-ticket-percent"></i></span>
            <span class="menu-item-label">Handling tickets</span>
          </a>
        </li>
      <?php endif; ?>
      
      <li class="--set-active-tables-html">
        <a href="tables.php">
          <span class="icon"><i class="mdi mdi-table"></i></span>
          <span class="menu-item-label">Tables</span>
        </a>
      </li>
      <li class="--set-active-forms-html">
        <a href="forms.php">
          <span class="icon"><i class="mdi mdi-square-edit-outline"></i></span>
          <span class="menu-item-label">Forms</span>
        </a>
      </li>
      <li class="--set-active-profile-html">
        <a href="profile.php">
          <span class="icon"><i class="mdi mdi-account-circle"></i></span>
          <span class="menu-item-label">Profile</span>
        </a>
      </li>
      <li>
        <a href="login.php">
          <span class="icon"><i class="mdi mdi-lock"></i></span>
          <span class="menu-item-label">Login</span>
        </a>
      </li>
      <li>
        <a class="dropdown">
          <span class="icon"><i class="mdi mdi-view-list"></i></span>
          <span class="menu-item-label">Submenus</span>
          <span class="icon"><i class="mdi mdi-plus"></i></span>
        </a>
        <ul>
          <li>
            <a href="#void">
              <span>Sub-item One</span>
            </a>
          </li>
          <li>
            <a href="#void">
              <span>Sub-item Two</span>
            </a>
          </li>
        </ul>
      </li>
    </ul>
    <p class="menu-label">About</p>
    <ul class="menu-list">
      <li>
        <a href="https://justboil.me" onclick="alert('Coming soon'); return false" target="_blank" class="has-icon">
          <span class="icon"><i class="mdi mdi-credit-card-outline"></i></span>
          <span class="menu-item-label">Premium Demo</span>
        </a>
      </li>
      <li>
        <a href="https://justboil.me/tailwind-admin-templates" class="has-icon">
          <span class="icon"><i class="mdi mdi-help-circle"></i></span>
          <span class="menu-item-label">About</span>
        </a>
      </li>
      <li>
        <a href="https://github.com/justboil/admin-one-tailwind" class="has-icon">
          <span class="icon"><i class="mdi mdi-github-circle"></i></span>
          <span class="menu-item-label">GitHub</span>
        </a>
      </li>
    </ul>
  </div>
</aside>