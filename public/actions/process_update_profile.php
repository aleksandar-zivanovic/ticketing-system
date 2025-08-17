<?php
$isNotPost       = $_SERVER["REQUEST_METHOD"] !== "POST";
$noFormSent      = empty($_POST["update_profile"]) && empty($_POST["update_pwd"]);
$invalidProfile  = isset($_POST["update_profile"]) && $_POST["update_profile"] !== "updateProfile";
$invalidPassword = isset($_POST["update_pwd"]) && $_POST["update_pwd"] !== "updatePassword";

if ($isNotPost || $noFormSent || $invalidProfile || $invalidPassword) {
    header("Location: user/user-ticket-listing.php");
    die;
}

session_start();
require_once '../../config/config.php';
require_once ROOT . 'helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

require_once ROOT . 'classes/User.php';

saveFormValuesToSession();

// Gets id of the member whose data should be updated.
$profileId = filter_input(INPUT_POST, "profile_id", FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1,]
]);

if ($profileId === false || $profileId !== (int) $_SESSION['user_id']) {
    logError($errorMessage("profile_id manually changed in form by the user {$_SESSION['user_email']} with IP: " . getIp()));
    http_response_code(403);
    die('Forbidden action!');
}

// Creates redirection
$redirect = function ($profileId) {
    header("Location: /ticketing-system/public/profile.php?user={$profileId}");
    die;
};

$user = new User();

if (isset($_POST["update_profile"]) && $_POST["update_profile"] === "updateProfile") {
    if (empty($_POST["fname"]) || empty($_POST["sname"])) {
        $_SESSION["fail"] = "Please fulfill all requred fields!";
        $redirect($profileId);
    }

    if (strlen($_POST["fname"]) < 3 || strlen($_POST["sname"]) < 3) {
        $_SESSION["fail"] = "First name and surname must be at least 3 characters long";
        $redirect($profileId);
    }

    try {
        if ($user->updateUserRow() === true) {
            $_SESSION["success"] = "Profile is updated!";
            $redirect($profileId);
        }
    } catch (\InvalidArgumentException $e) {
        $_SESSION["fail"] = $e->getMessage();
        $redirect($profileId);
    }
}

if (isset($_POST["update_pwd"]) && $_POST["update_pwd"] === "updatePassword") {
    if (
        empty($_POST["password_current"]) ||
        empty($_POST["password_new"]) ||
        empty($_POST["password_confirmation"]) ||
        strlen($_POST["password_current"]) < 6 ||
        strlen($_POST["password_new"]) < 6 ||
        strlen($_POST["password_confirmation"]) < 6
    ) {
        $_SESSION["fail"] = "Please fulfill all password fields with at least 6 characters long password!";
        $redirect($profileId);
    }

    if ($_POST["password_new"] !== $_POST["password_confirmation"]) {
        $_SESSION["fail"] = "New password and confirmation password don't match.";
        $redirect($profileId);
    }

    if ($_POST["password_new"] === $_POST["password_current"]) {
        $_SESSION["fail"] = "New password must be different from the old.";
        $redirect($profileId);
    }

    $user->email = cleanString($_SESSION["user_email"]);
    $passFromDb = $user->getPasswordByEmail();

    // Checks if the old password is entered correctly.
    if (password_verify($_POST["password_current"], $passFromDb) !== true) {
        $_SESSION["fail"] = "Current password is wrong!";
        $redirect($profileId);
    }

    $user->password = $_POST["password_new"];
    try {
        if ($user->updatePassword($profileId) === true) {
            $_SESSION["success"] = "New password is set.";
            $redirect($profileId);
        }
    } catch (\InvalidArgumentException $e) {
        $_SESSION["fail"] = $e->getMessage();
        $redirect($profileId);
    } catch (\RuntimeException $e) {
        $_SESSION["fail"] = $e->getMessage();
        $redirect($profileId);
    }
}
