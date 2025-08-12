<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST["update_profile"]) || $_POST["update_profile"] !== "updateProfile") {
    header("Location: user/user-ticket-listing.php");
    die;
}

session_start();
require_once '../../config/config.php';
require_once ROOT . 'helpers/functions.php';
requireLogin();
require_once ROOT . 'classes/User.php';

saveFormValuesToSession();


if (empty($_POST["fname"]) || empty($_POST["sname"])) {

    // Gets id of the member whose data should be updated.
    $profileId = filter_input(INPUT_POST, "profile_id", FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1,]
    ]);

    if ($profileId === false) {
        logError($errorMessage("profile_id manually changed in form by the user {$_SESSION['user_email']} with IP: " . getIp()));
        http_response_code(403);
        die('Forbidden action!');
    }

    $_SESSION["fail"] = "Please fulfill all requred fields!";
    header("Location: /ticketing-system/public/profile.php?user={$profileId}");
    die;
}

$user = new User();
$user->updateUserRow();
