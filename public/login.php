<?php
session_start();
require_once '../helpers/functions.php';

// handling successful verification message
handleSessionMessages('verification_status', true);