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
    <title>Resend Verification Code</title>
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
                <h2>Enter Email Address</h2>
            </div>
            <div class="row clearfix">
                <div class="">
                    <form action="../actions/resend-verification-code.php" method="POST">
                        <!-- email input -->
                        <div class="input_field"> <span><i aria-hidden="true" class="fa fa-envelope"></i></span>
                            <input type="email" name="email" placeholder="Email" value="<?php persist_input('email'); ?>" required />
                        </div>
                        <!-- submit button -->
                        <input class="button" type="submit" name="verification_code_form" value="Send new code" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>