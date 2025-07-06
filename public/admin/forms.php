<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forms - Admin One Tailwind CSS Admin Dashboard</title>

  <!-- Tailwind is included -->
  <link rel="stylesheet" href="../css/admin-one-main.css">
</head>
<body>

<div id="app">

<nav id="navbar-main" class="navbar is-fixed-top">
  <div class="navbar-brand">
    <a class="navbar-item mobile-aside-button">
      <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
    </a>
    <div class="navbar-item">
      <div class="control"><input placeholder="Search everywhere..." class="input"></div>
    </div>
  </div>
  <div class="navbar-brand is-right">
    <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
      <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
    </a>
  </div>
  <div class="navbar-menu" id="navbar-menu">
    <div class="navbar-end">
      <div class="navbar-item dropdown has-divider">
        <a class="navbar-link">
          <span class="icon"><i class="mdi mdi-menu"></i></span>
          <span>Sample Menu</span>
          <span class="icon">
            <i class="mdi mdi-chevron-down"></i>
          </span>
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
          <a class="navbar-item">
            <span class="icon"><i class="mdi mdi-logout"></i></span>
            <span>Log Out</span>
          </a>
        </div>
      </div>
      <div class="navbar-item dropdown has-divider has-user-avatar">
        <a class="navbar-link">
          <div class="user-avatar">
            <img src="https://avatars.dicebear.com/v2/initials/john-doe.svg" alt="John Doe" class="rounded-full">
          </div>
          <div class="is-user-name"><span>John Doe</span></div>
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
          <a class="navbar-item">
            <span class="icon"><i class="mdi mdi-logout"></i></span>
            <span>Log Out</span>
          </a>
        </div>
      </div>
      <a href="https://justboil.me/tailwind-admin-templates" class="navbar-item has-divider desktop-icon-only">
        <span class="icon"><i class="mdi mdi-help-circle-outline"></i></span>
        <span>About</span>
      </a>
      <a href="https://github.com/justboil/admin-one-tailwind" class="navbar-item has-divider desktop-icon-only">
        <span class="icon"><i class="mdi mdi-github-circle"></i></span>
        <span>GitHub</span>
      </a>
      <a title="Log out" class="navbar-item desktop-icon-only">
        <span class="icon"><i class="mdi mdi-logout"></i></span>
        <span>Log out</span>
      </a>
    </div>
  </div>
</nav>

<!-- side menu -->
<?php include_once '../../partials/_side-menu.php'; ?>

<section class="is-title-bar">
  <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
    <ul>
      <li>Admin</li>
      <li>Forms</li>
    </ul>
    <a href="https://justboil.me/" onclick="alert('Coming soon'); return false" target="_blank" class="button blue">
      <span class="icon"><i class="mdi mdi-credit-card-outline"></i></span>
      <span>Premium Demo</span>
    </a>
  </div>
</section>

<section class="is-hero-bar">
  <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
    <h1 class="title">
      Forms
    </h1>
    <button class="button light">Button</button>
  </div>
</section>

  <section class="section main-section">
    <div class="card mb-6">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-ballot"></i></span>
          Forms
        </p>
      </header>
      <div class="card-content">
        <form method="get">
          <div class="field">
            <label class="label">From</label>
            <div class="field-body">
              <div class="field">
                <div class="control icons-left">
                  <input class="input" type="text" placeholder="Name">
                  <span class="icon left"><i class="mdi mdi-account"></i></span>
                </div>
              </div>
              <div class="field">
                <div class="control icons-left icons-right">
                  <input class="input" type="email" placeholder="Email" value="alex@smith.com">
                  <span class="icon left"><i class="mdi mdi-mail"></i></span>
                  <span class="icon right"><i class="mdi mdi-check"></i></span>
                </div>
              </div>
            </div>
          </div>
          <div class="field">
            <div class="field-body">
              <div class="field">
                <div class="field addons">
                  <div class="control">
                    <input class="input" value="+44" size="3" readonly>
                  </div>
                  <div class="control expanded">
                    <input class="input" type="tel" placeholder="Your phone number">
                  </div>
                </div>
                <p class="help">Do not enter the first zero</p>
              </div>
            </div>
          </div>
          <div class="field">
            <label class="label">Department</label>
            <div class="control">
              <div class="select">
                <select>
                  <option>Business development</option>
                  <option>Marketing</option>
                  <option>Sales</option>
                </select>
              </div>
            </div>
          </div>
          <hr>
          <div class="field">
            <label class="label">Subject</label>

            <div class="control">
              <input class="input" type="text" placeholder="e.g. Partnership opportunity">
            </div>
            <p class="help">
              This field is required
            </p>
          </div>

          <div class="field">
            <label class="label">Question</label>
            <div class="control">
              <textarea class="textarea" placeholder="Explain how we can help you"></textarea>
            </div>
          </div>
          <hr>

          <div class="field grouped">
            <div class="control">
              <button type="submit" class="button green">
                Submit
              </button>
            </div>
            <div class="control">
              <button type="reset" class="button red">
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-ballot-outline"></i></span>
          Custom elements
        </p>
      </header>
      <div class="card-content">
        <div class="field">
          <label class="label">Checkbox</label>
          <div class="field-body">
            <div class="field grouped multiline">
              <div class="control">
                <label class="checkbox"><input type="checkbox" value="lorem" checked>
                  <span class="check"></span>
                  <span class="control-label">Lorem</span>
                </label>
              </div>
              <div class="control">
                <label class="checkbox"><input type="checkbox" value="ipsum">
                  <span class="check"></span>
                  <span class="control-label">Ipsum</span>
                </label>
              </div>
              <div class="control">
                <label class="checkbox"><input type="checkbox" value="dolore">
                  <span class="check is-primary"></span>
                  <span class="control-label">Dolore</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <hr>
        <div class="field">
          <label class="label">Radio</label>
          <div class="field-body">
            <div class="field grouped multiline">
              <div class="control">
                <label class="radio">
                  <input type="radio" name="sample-radio" value="one" checked>
                  <span class="check"></span>
                  <span class="control-label">One</span>
                </label>
              </div>
              <div class="control">
                <label class="radio">
                  <input type="radio" name="sample-radio" value="two">
                  <span class="check"></span>
                  <span class="control-label">Two</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <hr>
        <div class="field">
          <label class="label">Switch</label>
          <div class="field-body">
            <div class="field">
              <label class="switch">
                <input type="checkbox" value="false">
                <span class="check"></span>
                <span class="control-label">Default</span>
              </label>
            </div>
          </div>
        </div>
        <hr>
        <div class="field">
          <label class="label">File</label>
          <div class="field-body">
            <div class="field file">
              <label class="upload control">
                <a class="button blue">
                  Upload
                </a>
                <input type="file">
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php 
  // Import edit modal
  include_once '../../partials/_edit_modal.php';

  // Import delete modal
  include_once '../../partials/_delete_modal.php';

  // Import footer
  include_once '../../partials/_footer.php'; 
  ?>
  
</div>

</body>
</html>
