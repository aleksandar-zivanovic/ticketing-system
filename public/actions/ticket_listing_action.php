<?php
require_once '../../helpers/functions.php';

// Check if a visitor is logged in.
requireLogin();

require_once '../../controllers/TicketListingController.php';
$controller = new TicketListingController();

// Get sorting and ordering parameters
$sortBy = null;
$table = null;
if (isset($_GET['sort']) && !empty(trim($_GET['sort']))) {
    $sortByValidation = $controller->validateSortBy($_GET['sort']);
    if ($sortByValidation["allowedSort"] === true) {
        $sortBy = $sortByValidation["cleanSortBy"];
        $table = $sortByValidation["table"];
    }
}

$orderBy = (isset($_GET["order_by"]) && trim($_GET["order_by"]) === "oldest") ?  "oldest" : "newest";

// Set results per page
if (isset($_GET['limit'])) {
    if ($_GET['limit'] !== "all") {
        $limit = $controller->validateId($_GET["limit"]);
        $_SESSION['limit'] = $limit = $limit === false ? 0 : $limit;
    } else {
        $limit = $_SESSION['limit'] = 0;
    }
} elseif (!isset($_GET['limit']) && isset($_SESSION['limit'])) {
    $limit = $_SESSION['limit'];
} else {
    $limit = 10;
    $_SESSION['limit'] = 10;
}

// If $ticketsIHandle is not set, set it to false
$ticketsIHandle = $ticketsIHandle ?? false;

$tlcData = $controller->prepareTicketsListingData($panel, $sortBy, $orderBy, $table, $limit, $ticketsIHandle);

$data        = $tlcData["data"];
$totalItems  = $tlcData["totalItems"];
$currentPage = $tlcData["currentPage"];
$totalPages  = $tlcData["totalPages"];
$pagination  = $tlcData["pagination"];
$statuses    = $tlcData["statuses"];
$priorities  = $tlcData["priorities"];
$departments = $tlcData["departments"];
