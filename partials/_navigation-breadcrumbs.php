<section class="is-title-bar">
  <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
    <ul>
      <li>
        <a href="
        <?php echo $panel === "admin" ? "admin-ticket-listing.php" : "../user/user-ticket-listing.php" ?>
        "><?= ucfirst($panel) ?></a>
      </li>
      <li><?= $page ?></li>
    </ul>
  </div>
</section>