<aside class="aside is-placed-left is-expanded">
  <div class="aside-tools">
    <div>
      Ticketing <b class="font-black">System</b>
    </div>
  </div>
  <div class="menu is-menu-main">
    <p class="menu-label">General</p>
    <ul class="menu-list">
      <?php if ($_SESSION['user_role'] === "admin") : ?>
        <li class="--set-active-tables-html">
          <a href="/ticketing-system/admin-dashboard.php">
            <span class="icon"><i class="mdi mdi-view-dashboard-edit"></i></span>
            <span class="menu-item-label">Dashboard</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="--set-active-tables-html">
        <a href="/ticketing-system/my-dashboard.php">
          <span class="icon"><i class="mdi mdi-view-dashboard"></i></span>
          <span class="menu-item-label">My Dashboard</span>
        </a>
      </li>
    </ul>
    <p class="menu-label">Menu</p>
    <ul class="menu-list">
      <?php if ($_SESSION['user_role'] === "admin") : ?>
        <li class="--set-active-tables-html">
          <a href="/ticketing-system/admin/admin-ticket-listing.php">
            <span class="icon"><i class="mdi mdi-ticket-confirmation"></i></span>
            <span class="menu-item-label">Tickets</span>
          </a>
        </li>
      <?php endif; ?>

      <li class="--set-active-tables-html">
        <a href="/ticketing-system/user/user-ticket-listing.php">
          <span class="icon"><i class="mdi mdi-ticket-account"></i></span>
          <span class="menu-item-label">My Tickets</span>
        </a>
      </li>

      <?php if ($_SESSION['user_role'] === "admin") : ?>
        <li class="--set-active-tables-html">
          <a href="/ticketing-system/admin/admin-tickets-i-handle.php">
            <span class="icon"><i class="mdi mdi-ticket-percent"></i></span>
            <span class="menu-item-label">Handling tickets</span>
          </a>
        </li>
      <?php endif; ?>
      <li class="--set-active-profile-html">
        <a href="/ticketing-system/profile.php?user=<?= cleanString($_SESSION["user_id"]) ?>">
          <span class="icon"><i class="mdi mdi-account-circle"></i></span>
          <span class="menu-item-label">Profile</span>
        </a>
      </li>
    </ul>
    <p class="menu-label">Contact</p>
    <ul class="menu-list">
      <li>
        <a href="" onclick="showContact(); return false" class="has-icon">
          <span class="icon"><i class="mdi mdi-phone-outgoing-outline"></i></span>
          <span class="menu-item-label">Contact</span>
        </a>
      </li>
    </ul>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function showContact() {
      Swal.fire({
        title: "<strong>Contact</strong>",
        icon: "info",
        html: `
        <span class="mdi mdi-cellphone-check"></span> 012 3456789</br>
        <span class="mdi mdi-email"></span> example@example.com</br>
  `,
        showCloseButton: true,
      });
    }
  </script>
</aside>