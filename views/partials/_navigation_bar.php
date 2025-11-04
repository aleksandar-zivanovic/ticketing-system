<nav id="navbar-main" class="navbar is-fixed-top">

  <!-- search bar -->
  <?php require_once '_navigation_search.php'; ?>

  <div class="navbar-brand is-right">
    <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
      <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
    </a>
  </div>
  <div class="navbar-menu" id="navbar-menu">
    <div class="navbar-end">
      <!-- profile menu -->
      <?php require_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_profile_menu.php'; ?>

      <!-- navigation buttons -->
      <?php require_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_buttons.php'; ?>
    </div>
  </div>
</nav>

<!-- search results container -->
<div id="searchResultsContainer" class="m-5" style="display: none;">
  <div id="searchResults" class="m-4 bg-gray-100 border border-gray-300"></div>
  <div id="clearButtonWrapper" class="m-4">
    <?php renderingButton(
      name: 'clearSearch',
      value: 'Clear Search Results',
      textColor: 'text-blue-400',
      bgColor: 'bg-gray-100',
      hoverBgColor: 'hover:bg-gray-200',
      otherClasses: 'px-4 py-2 border border-blue-300 border-solid border-2 rounded',
      otherAttributes: 'onclick="clearSearchResults()"',
      type: 'button'
    ); ?>
  </div>
  <hr class="border-t border-gray-300">
</div>