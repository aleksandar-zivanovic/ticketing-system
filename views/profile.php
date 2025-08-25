<?php
require_once ROOT . 'helpers' . DS . 'view_helpers.php';

$panel = "profile";
$page  = "Profile page";
?>
<!DOCTYPE html>
<html lang="en" class="">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page ?></title>

  <!-- Tailwind is included -->
  <link rel="stylesheet" href="/ticketing-system/public/css/tailwind-output.css">
  <link rel="stylesheet" href="/ticketing-system/public/css/admin-one-main.css">
  <link rel="stylesheet" href="/ticketing-system/public/css/font-awesome.min.css">
</head>

<body>

  <div id="app">

    <?php
    // import header navigation bar
    include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_bar.php';

    // import side menu bar
    include_once ROOT . 'views' . DS . 'partials' . DS . '_side_menu.php';

    // import breadcrumbs
    include_once ROOT . 'views' . DS . 'partials' . DS . '_navigation_breadcrumbs.php';

    // import session messages
    include_once ROOT . 'views' . DS . 'partials' . DS . '_session_messages.php';
    ?>


    <section class="is-hero-bar">
      <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1 class="title">
          Profile
        </h1>
      </div>
    </section>

    <section class="section main-section">
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <div class="card">
          <header class="card-header">
            <p class="card-header-title">
              <span class="icon"><i class="mdi mdi-account-circle"></i></span>
              Edit Profile
            </p>
          </header>
          <div class="card-content">
            <form method="POST" action="/ticketing-system/public/actions/profile_update_action.php">
              <!-- <div class="field">
                <label class="label">Avatar</label>
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
              </div> -->
              <hr>
              <div class="field">
                <label class="label">First name</label>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <?php renderingInputField(null, "fname", "text", $theUser['name'], $theUser['name']) ?>
                    </div>
                    <p class="help">Required. Your first name (at least 3 characters long)</p>
                  </div>
                </div>
              </div>
              <div class="field">
                <label class="label">Family name</label>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <?php renderingInputField(null, "sname", "text", $theUser['surname'], $theUser['surname']) ?>
                    </div>
                    <p class="help">Required. Your family name (at least 3 characters long)</p>
                  </div>
                </div>
              </div>
              <div class="field">
                <label class="label">Phone</label>
                <div class="field-body">
                  <div class="field">
                    <div class="control">
                      <?php renderingInputField(
                        null,
                        "phone",
                        "tel",
                        $theUser['phone'],
                        $theUser['phone'],
                        'pattern=\+?[0-9]{7,15}'
                      )
                      ?>
                    </div>
                    <p class="help">Required. Format: +381612345678 or 061234567.</p>
                  </div>
                </div>
              </div>
              <?php
              // Email can be edited by:
              // 1. an admin if the target user is not an admin
              // 2. an admin if it is his/her own profile
              if (
                ($_SESSION["user_role"] === "admin" && $theUser["role_id"] !== 3) ||
                ($theUser["role_id"] === 3 && $theUser["id"] === $_SESSION["user_id"])
              ) :
              ?>
                <div class="field">
                  <label class="label">E-mail</label>
                  <div class="field-body">
                    <div class="field">
                      <div class="control">
                        <input type="text" autocomplete="on" name="email" value="<?php persist_input($theUser['email']) ?>" class="input">
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <input type="text" name="profile_id" value="<?= $id ?>" hidden>
              <hr>
              <div class="field">
                <div class="control">
                  <button type="submit" name="update_profile" value="updateProfile" class="button green">
                    Submit
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="card">
          <header class="card-header">
            <p class="card-header-title">
              <span class="icon"><i class="mdi mdi-account"></i></span>
              Profile
            </p>
          </header>
          <div class="card-content">
            <!-- <div class="image w-48 h-48 mx-auto">
              <img src="https://api.dicebear.com/9.x/lorelei/svg?seed=user" alt="John Doe" class="rounded-full">
            </div>
            <hr> -->
            <div class="field">
              <label class="label">First name</label>
              <div class="control">
                <input type="text" readonly value="<?= $theUser['name'] ?>" class="input is-static">
              </div>
            </div>
            <div class="field">
              <label class="label">Family name</label>
              <div class="control">
                <input type="text" readonly value="<?= $theUser['surname'] ?>" class="input is-static">
              </div>
            </div>
            <div class="field">
              <label class="label">Phone</label>
              <div class="control">
                <input type="text" readonly value="<?= $theUser['phone'] ?>" class="input is-static">
              </div>
            </div>
            <hr>
            <div class="field">
              <label class="label">E-mail</label>
              <div class="control">
                <input type="text" readonly value="<?= $theUser['email'] ?>" class="input is-static">
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php if ($id === $session_user_id) : ?>
        <div class="card">
          <header class="card-header">
            <p class="card-header-title">
              <span class="icon"><i class="mdi mdi-lock"></i></span>
              Change Password
            </p>
          </header>
          <div class="card-content">
            <form method="POST" action="/ticketing-system/public/actions/profile_update_action.php">
              <div class="field">
                <label class="label">Current password</label>
                <div class="control">
                  <input type="password" name="password_current" class="input" minlength="6" required>
                </div>
                <p class="help">Required. Your current password</p>
              </div>
              <hr>
              <div class="field">
                <label class="label">New password</label>
                <div class="control">
                  <input type="password" name="password_new" class="input" minlength="6" required>
                </div>
                <p class="help">Required. New password</p>
              </div>
              <div class="field">
                <label class="label">Confirm password</label>
                <div class="control">
                  <input type="password" name="password_confirmation" class="input" minlength="6" required>
                </div>
                <p class="help">Required. New password one more time</p>
              </div>
              <input type="text" name="profile_id" value="<?= $id ?>" hidden>
              <hr>
              <div class="field">
                <div class="control">
                  <button type="submit" name="update_pwd" value="updatePassword" class="button green">
                    Submit
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <?php
    // import footer
    include_once ROOT . 'views' . DS . 'partials' . DS . '_footer.php';
    ?>

</body>

</html>