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
    <title>Registration page</title>
    <link rel="stylesheet" href="../css/form.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/tailwind-output.css">
</head>

<body>
    <div class="max-w-4xl mx-auto font-[sans-serif] p-6">
        <div class="text-center mb-16">
            <a href="javascript:void(0)"><img src="https://readymadeui.com/readymadeui.svg" alt="logo" class='w-52 inline-block' />
            </a>
            <h4 class="text-gray-800 text-base font-semibold mt-6">Sign up into your account</h4>
        </div>

        <?php
        if (!empty($_SESSION["fail"]) || isset($_SESSION['verification_status'])):
        ?>
        <div class="bg-red-100 m-6 p-2 text-center">
            <?php
            // handling registration error message
            handleSessionMessages("fail", true);

            // handling email verification error message
            handleSessionMessages("verification_status", true);
            ?>
        </div>
        <?php endif; ?>

        <form action="../actions/process_user_action.php" method="POST">
            <div class="grid sm:grid-cols-2 gap-8">

                <?php
                // name filed
                renderingInputField("Name:", "name", "text", "Enter your name");

                // surname field
                renderingInputField("Surname:", "surname", "text", "Enter your surname");

                // email field
                renderingInputField("Email:", "email", "email", "Enter your email address");

                // phone field
                renderingInputField("Phone number:", "phone", "text", "Enter your phone number");

                // password field
                renderingInputField("Enter password:", "password", "password", "Enter your password");

                // pasword confirmation field
                renderingInputField("Confirm Password:", "rpassword", "password", "Repeat the password");
                ?>
                <div class="flex items-center">
                    <?php
                    // agree checkbox
                    renderingCheckboxField('agree_terms', null, 'I accept', 'https://www.target.com/c/terms-conditions/-/N-4sr7l', 'Terms and Conditions');
                    ?>
                </div>
            </div>
            <div class="!mt-12">
            <?php 
            // submit button
            renderingSubmitButton('user_action', 'Register');
            ?>
          </div>
            <p class="text-gray-800 text-sm mt-6 text-center">Already have an account? <a href="login.php" class="text-blue-600 font-semibold hover:underline ml-1">Login here</a></p>
        </form>
    </div>
</body>

</html>