<?php
session_start();
require_once '../../config/config.php';

$ticketsIHandle = true;

// Sets the panel (admin or user)
$panel = "admin";

require_once '../../partials/_ticket-listing-init.php';