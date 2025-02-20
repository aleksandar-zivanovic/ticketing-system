<?php
session_start();
require_once '../../helpers/functions.php';
require_once '../../classes/User.php';
require_once '../../classes/Ticket.php';
require_once '../../classes/Department.php';
require_once '../../classes/Priority.php';
require_once '../../classes/Status.php';
require_once '../../classes/Pagination.php';

// Sets the panel (admin or user)
$panel = "user";

// Set $page and $data varaiables
$page = fileName(__FILE__);
$data = true;

// Initialize allowed filter values for tickets
$status = new Status();
$statuses = $status->getAllStatusNames();

$priority = new Priority();
$priorities = $priority->getAllPriorityNames();

$department = new Department();
$departments = $department->getAllDepartmentNames();

// Set allowed values list for fetchAllTickets() method
$allowedValues = array_merge(
  ["statuses" => $statuses], 
  ["priorities" => $priorities], 
  ["departments" => $departments],
);

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

// Call fetchAllTickets() method
$ticket = new Ticket();
$data = $ticket->fetchAllTickets(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, limit: $limit, userId: $_SESSION['user_id']);

// Pagination proccessing
$ticket2 = new Ticket();
$totalItems = $ticket2->countAllTickets(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, userId: $_SESSION['user_id']);

// Sets results per page
if (isset($_GET["limit"])) {
  $limit = intval(cleanString($_GET["limit"]));
}elseif (!isset($_GET["limit"]) && isset($_SESSION["limit"])) {
  $limit = intval(cleanString($_SESSION["limit"]));
} else {
  $limit = 10;
}

$pagination = new Pagination($limit, $totalItems);
$currentPage = $pagination->getCurrentPage();
$totalPages = $pagination->getTotalPages();
?>