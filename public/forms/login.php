<?php
session_start();
require_once '../../helpers/functions.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="../css/form.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/tailwind-output.css">
</head>

<body>
    <div class="w-full sm:w-3/4 lg:w-2/5 mx-auto font-[sans-serif] p-6">
        <div class="text-center mb-16">
            <a href="javascript:void(0)"><img src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-52 inline-block' />
            </a>
            <h4 class="text-gray-800 text-base font-semibold mt-6">Login to your account</h4>
        </div>

        <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="bg-red-100 m-6 p-2 text-center">
            <?php
            // handling login error message
            handleSessionMessages('error_message', true);
            ?>
        </div>
        <?php endif; ?>

        <form action="../actions/process_user_action.php" method="POST">
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
            renderingSubmitButton('user_action', 'Login');
            ?>
            </div>

            <div class="mt-6">
                <p class="text-gray-800 text-sm text-center">Forgot password? <a href="reset-password.php" class="text-blue-600 font-semibold hover:underline ml-1">Reset password.</a></p>

                <p class="text-gray-800 text-sm mt-2 text-center">Don't have and account? <a href="register.php" class="text-blue-600 font-semibold hover:underline ml-1">Register here.</a></p>
            </div>
        </form>
    </div>
</body>

</html>