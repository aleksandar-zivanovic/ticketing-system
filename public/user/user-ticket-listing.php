<?php
session_start();

// Sets the panel (admin or user)
$panel = "user";

require_once '../../config/config.php';
require_once '../../partials/_ticket-listing-init.php';