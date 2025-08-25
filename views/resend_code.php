<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification Code</title>
    <link rel="stylesheet" href="/ticketing-system/public/css/form.css">
    <link rel="stylesheet" href="/ticketing-system/public/css/font-awesome.min.css">
    <link rel="stylesheet" href="/ticketing-system/public/css/tailwind-output.css">
</head>

<body>
    <div class="app">
        <div class="messages_container">
            <?php require_once ROOT . 'views' . DS . 'partials' . DS . '_session_messages.php'; ?>
        </div>
        <div class="form_wrapper">
            <div class="form_container">
                <div class="title_container">
                    <h2>Enter Email Address</h2>
                </div>
                <div class="row clearfix">
                    <div class="">
                        <form action="resend_code_action.php" method="POST">
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
        <div class="footer flex justify-center items-center mt-32">
            <?php require_once ROOT . 'views' . DS . 'partials' . DS . '_footer.php'; ?>
        </div>
    </div>
</body>

</html>