<div class="navbar-brand">
  <a class="navbar-item mobile-aside-button">
    <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
  </a>
  <div class="navbar-item ml-4 p-4 shadow-md shadow-gray-200 rounded-md">
    <form action="">
      <div class="flex flex-row gap-2">
        <select name="searchSelect" id="searchSelect" class="bg-white">
          <?php
          $selectOptions = ["Ticket body", "Title", "Ticket ID", "Name/Surname", "User ID", "Email"];
          foreach ($selectOptions as $selectOption) :
            $value = str_replace([" ", "/"], "", $selectOption);
          ?>
            <option value="<?= $value ?>" <?= addSelectedTag($value) ?>><?= $selectOption ?></option>
          <?php
          endforeach;
          ?>
        </select>
        <input id="searchInput" class="input" placeholder="Search everywhere..." type="text" name="searchInput" value="<?php echo isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : ''; ?>">
      </div>
    </form>
  </div>
</div>