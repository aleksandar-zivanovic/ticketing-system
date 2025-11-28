<?php
if (isLoggedIn()) {
    // Redirect to user ticket listing if already logged in
    if (trim($_SESSION['user_role']) === "admin") {
        redirectAndDie(BASE_URL . "admin/admin-ticket-listing.php");
    } else {
        redirectAndDie(BASE_URL . "user/user-ticket-listing.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/form.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/tailwind-output.css">
</head>

<body>
    <div class="w-full sm:w-3/4 lg:w-2/5 mx-auto font-[sans-serif] p-6">
        <div class="text-center mb-16">
            <a href="javascript:void(0)"><img src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-52 inline-block' />
            </a>
            <h4 class="text-gray-800 text-base font-semibold mt-6">Login to your account</h4>
        </div>

        <?php
        // import session messages
        include_once ROOT . 'views' . DS . 'partials' . DS . '_session_messages.php';
        ?>

        <form action="<?= BASE_URL ?>login_action.php" method="POST">
            <div class="grid gap-8">
                <?php
                // email field
                renderingInputField("Email:", "email", "email", "Enter your email address");

                // password field
                renderingInputField("Password:", "password", "password", "Enter your password");
                ?>
            </div>
            <div class="!mt-12">
                <?php
                // submit button
                renderingButton('user_action', 'Login');
                ?>
            </div>

            <div class="mt-6">
                <p class="text-gray-800 text-sm text-center">Forgot password? <a href="reset-password.php" class="text-blue-600 font-semibold hover:underline ml-1">Reset password.</a></p>

                <p class="text-gray-800 text-sm mt-2 text-center">Haven't received verification email? <a href="resend-code.php" class="text-blue-600 font-semibold hover:underline ml-1">Resend verification email.</a></p>

                <p class="text-gray-800 text-sm mt-2 text-center">Don't have an account? <a href="register.php" class="text-blue-600 font-semibold hover:underline ml-1">Register here.</a></p>
            </div>
        </form>
    </div>
</body>

</html>