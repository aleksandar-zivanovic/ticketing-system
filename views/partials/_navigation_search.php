<div class="navbar-brand">
  <a class="navbar-item mobile-aside-button">
    <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
  </a>
  <div class="navbar-item ml-4 p-4 shadow-md shadow-gray-200 rounded-md">
    <form action="">
      <div class="flex flex-row gap-2">
        <select name="search" id="search" class="bg-white">
          <?php
          $titles = ["Ticket body", "Title", "Ticket ID", "Name/Surname", "User ID", "Email"];
          foreach ($titles as $title) :
            $value = str_replace([" ", "/"], "", $title);
          ?>
            <option value="<?= $value ?>" <?= addSelectedTag($value) ?>><?= $title ?></option>
          <?php
          endforeach;
          ?>
        </select>
        <input placeholder="Search everywhere..." class="input" type="text" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : ''; ?>">
      </div>
    </form>
  </div>
</div>