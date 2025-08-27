<?php
// Checks if the user is logged and if has the `admin` role
checkAuthorization("admin", "../");

require_once '../../classes/User.php';
require_once '../../classes/Department.php';
require_once '../../classes/Priority.php';
require_once '../../classes/Status.php';
require_once '../../services/TicketService.php';

// Sets the panel (admin or user)
$panel = "admin";

// Set $page and $data varaiables
$page = str_replace("Controller", "", fileName(__FILE__));
$page = implode(" ", preg_split('/(?=[A-Z])/', $page));
$data = true;

// Initializes allowed filter values for tickets
$allTicketFilterData = loadTicketFilterData();
$statuses    = $allTicketFilterData["statuses"];
$priorities  = $allTicketFilterData["priorities"];
$departments = $allTicketFilterData["departments"];

// Sets allowed values list for fetchAllTickets() method
$allowedValues = buildAllowedTicketValues($allTicketFilterData);

// Get sorting and ordering parameters
if (isset($_GET['order_by'])) {
  $orderBy = cleanString(filter_input(INPUT_GET, 'order_by', FILTER_DEFAULT));
  $_SESSION['order_by'] = $orderBy;
} elseif (!isset($_GET['order_by']) && isset($_SESSION['order_by'])) {
  $orderBy = $_SESSION['order_by'];
} else {
  $orderBy = "newest";
}

$sortBy = isset($_GET['sort']) ? filter_input(INPUT_GET, 'sort', FILTER_DEFAULT) : null;

// Set results per page
if (isset($_GET['limit'])) {
  if ($_GET['limit'] !== "all") {
    $limit = intval(filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT));
    if ($limit < 0) $limit = 0;
    $_SESSION['limit'] = $limit;
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

$ticketService = new TicketService();

// Fetch tickets for pagination
$data = $ticketService->fetchTicketsForPagination(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, limit: $limit, handledByMe: $ticketsIHandle);

// Count total tickets for pagination
$totalItems = $ticketService->countAllTicketsForPagination(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, handledByMe: $ticketsIHandle);

// Get pagination data
$paginationData = $ticketService->getPaginationData(limit: $limit, totalItems: $totalItems);
$currentPage = $paginationData['currentPage'];
$totalPages = $paginationData['totalPages'];
$pagination = $paginationData['pagination'];
?>