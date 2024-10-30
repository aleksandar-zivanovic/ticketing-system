<?php
    session_start();
    require_once('../../helpers/functions.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration page</title>
    <link rel="stylesheet" href="../css/form.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
</head>
<body>
    <div class="form_wrapper">
        <?php
            // handling registration error message
            handleSessionMessages('error_message', true);

            // handling email verification error message
            handleSessionMessages('verification_status', true);
        ?>
        <div class="form_container">
            <div class="title_container">
                <h2>Registration Form</h2>
            </div>
            <div class="row clearfix">
                <div class="">
                    <form action="../actions/process_registration.php" method="POST">
                        <!-- email input -->
                        <div class="input_field"> <span><i aria-hidden="true" class="fa fa-envelope"></i></span>
                            <input type="text" name="email" placeholder="Email" value="<?php persist_input('email'); ?>" required />
                        </div>
                        <!-- password input -->
                        <div class="input_field"> <span><i aria-hidden="true" class="fa fa-lock"></i></span>
                            <input type="password" name="password" placeholder="Password" value="<?php persist_input('password') ?>" required />
                        </div>
                        <!-- retype password input -->
                        <div class="input_field"> <span><i aria-hidden="true" class="fa fa-lock"></i></span>
                            <input type="password" name="rpassword" placeholder="Re-type Password" value="<?php persist_input('rpassword') ?>" required />
                        </div>
                        <div class="row clearfix">
                            <!-- name input -->
                            <div class="col_half">
                                <div class="input_field"> <span><i aria-hidden="true" class="fa fa-user"></i></span>
                                    <input type="text" name="name" placeholder="First Name" value="<?php persist_input('name') ?>" required />
                                </div>
                            </div>
                            <!-- surname input -->
                            <div class="col_half">
                                <div class="input_field"> <span><i aria-hidden="true" class="fa fa-user"></i></span>
                                    <input type="text" name="surname" placeholder="Last Name" value="<?php persist_input('surname') ?>" required />
                                </div>
                            </div>
                        </div>
                        <!-- phone input -->
                        <div class="input_field"> <span><i aria-hidden="true" class="fa fa-phone"></i></span>
                            <input type="text" name="phone" placeholder="Phone number"  value="<?php persist_input('phone') ?>" required />
                        </div>
                        <!-- agree checkbox -->
                        <div class="input_field checkbox_option">
                            <input type="checkbox" id="cb1" name="agree_terms">
                            <label for="cb1">I agree with terms and conditions</label>
                        </div>
                        <!-- submit button -->
                        <input class="button" type="submit" name="registration_form" value="Register" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>