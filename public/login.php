<?php
session_start();

// handling successful verification message
if (isset($_SESSION['verification_status'])) {
    echo $_SESSION['verification_status'];
    unset($_SESSION['verification_status']);
}