<?php
$isNotPost        = $_SERVER["REQUEST_METHOD"] !== "POST";
$noFormSent       = !isset($_POST["update_profile"]) && !isset($_POST["update_pwd"]);
$invalidProfile   = isset($_POST["update_profile"]) && trim($_POST["update_profile"]) !== "updateProfile";
$invalidPassword  = isset($_POST["update_pwd"]) && trim($_POST["update_pwd"]) !== "updatePassword";
$invalidRequest   = isset($_POST["update_profile"]) && isset($_POST["update_pwd"]);
$noProfileIdSent  = !isset($_POST["profile_id"]) || empty(trim($_POST["profile_id"]));

if ($isNotPost || $noFormSent || $invalidProfile || $invalidPassword || $invalidRequest || $noProfileIdSent) {
    header("Location: /ticketing-system/public/user/user-ticket-listing.php");
    die;
}

session_start();
// require_once '../ticketing-system/config/config.php';
require_once '../../config/config.php';
require_once ROOT . 'helpers/functions.php';

// Checks if a visitor is logged in.
requireLogin();

require_once ROOT . 'classes/User.php';
require_once ROOT . 'controllers/ProfileUpdateController.php';

saveFormValuesToSession();

// Sets action type
$action = (isset($_POST["update_profile"]) && trim($_POST["update_profile"]) === "updateProfile") ? "updateProfile" : "updatePassword";

$controller = new ProfileUpdateController();

// Gets id of the member whose data should be updated.
$controller->sanitizedProfileId = $controller->validateId($_POST["profile_id"]);
if ($controller->sanitizedProfileId === false) {
    logError("profile_id manually changed in form by the user {$_SESSION['user_email']} with IP: " . getIp());
    redirectAndDie("/ticketing-system/public/user/user-ticket-listing.php", "Invalid profile ID.");
}

// Creates redirection URL
$redirectUrl = "/ticketing-system/public/profile.php?user={$controller->sanitizedProfileId}";


// Updates profile
if ($action === "updateProfile") {
    // Validates if all required fields are filled
    if (
        (empty(trim($_POST["fname"])) || empty(trim($_POST["sname"]))) &&
        (isset($_POST["email"]) && empty(trim($_POST["email"])))
    ) {
        redirectAndDie($redirectUrl, "Please fulfill all required fields!");
    }
}

// Updates password
if ($action === "updatePassword") {
    // Validates if all password fields are filled
    if (
        empty($_POST["password_current"]) ||
        empty($_POST["password_new"]) ||
        empty($_POST["password_confirmation"])
    ) {
        redirectAndDie($redirectUrl, "Please fulfill all password fields!");
    }
}

$validationResult = $controller->validateRequest($_POST, $_SESSION['user_id'],  $_SESSION["user_email"], $action);
if ($validationResult["success"] === false) {
    redirectAndDie($redirectUrl, $validationResult["message"]);
}

try {
    $controller->update($action);

    if ($action === "updatePassword") {
        $_SESSION = [];
        redirectAndDie("../forms/login.php", "Password changed successfully. Please log in again.", "success");
    } else {
        redirectAndDie($redirectUrl, "Profile updated successfully. Log in again to see the changes.", "success");
    }
} catch (\Throwable $th) {
    redirectAndDie($redirectUrl, "Profile update failed. Please try again later.");
}
