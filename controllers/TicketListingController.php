<?php
require_once '../../controllers/BaseController.php';
require_once '../../classes/User.php';
require_once '../../classes/Department.php';
require_once '../../classes/Priority.php';
require_once '../../classes/Status.php';
require_once '../../services/TicketListingService.php';

class TicketListingController extends BaseController
{
  public function validateSortBy(?string $sortBy): array
  {
    // Trims and sanatizates
    $sortBy = cleanString($sortBy);

    $allowedValues = loadTicketFilterData();

    $allowedSort = false;
    if ($sortBy === null || $sortBy === "all") {
      $allowedSort = true;
      $table = null;
    } else {
      foreach ($allowedValues as $key => $value) {
        if (in_array($sortBy, $value)) {
          $allowedSort = true;
          $table = $key;
          break;
        }
      }
    }

    return ["allowedSort" => $allowedSort, "table" => $table, "cleanSortBy" => $sortBy];
  }

  /**
   * Prepares tickets listing data for the partial template.
   *
   * - Fetches tickets based on panel type (admin/user) and optional "handled by me" filter.
   * - Applies sorting, filtering, and pagination.
   * - Loads supporting data for dropdown filters: statuses, priorities, and departments.
   *
   * @param string $panel "admin" or "user" – determines ticket scope and links
   * @param string $sortBy Filter to sort by (status, priority, department, etc.)
   * @param string $orderBy "ASC" | "DESC" or "newest" | "oldest"
   * @param string|null $table Column from tickets table
   * @param int $limit Number of tickets per page
   * @param bool $ticketsIHandle Optional. If true, admin panel only: fetch tickets handled by current user
   * @return array{
   *     data: array,           // List of tickets matching filters
   *     totalItems: int,       // Total number of tickets matching filters
   *     currentPage: int,      // Current pagination page
   *     totalPages: int,       // Total pagination pages
   *     pagination: array,     // Array of pagination links
   *     statuses: array,       // Allowed ticket statuses
   *     priorities: array,     // Allowed ticket priorities
   *     departments: array     // Allowed ticket departments
   * }
   * @see TicketService::fetchTicketsForPagination()
   * @see TicketService::countAllTicketsForPagination()
   */
  public function prepareTicketsListingData(string $panel, ?string $sortBy, string $orderBy, ?string $table, int $limit, bool $ticketsIHandle = false): array
  {
    // Initializes allowed filter values for tickets
    $allTicketFilterData = loadTicketFilterData();
    $statuses    = $allTicketFilterData["statuses"];
    $priorities  = $allTicketFilterData["priorities"];
    $departments = $allTicketFilterData["departments"];

    // Sets allowed values list for fetchAllTickets() method
    $allowedValues = buildAllowedTicketValues($allTicketFilterData);

    $service = new TicketListingService();

    // Fetch tickets and count total tickets based on the panel type
    if ($panel === "admin") {
      // Fetch tickets for pagination
      $data = $service->fetchTicketsForPagination(orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit, handledByMe: $ticketsIHandle);

      // Count total tickets for pagination
      $totalItems = $service->countAllTicketsForPagination(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, handledByMe: $ticketsIHandle);
    }

    if ($panel === "user") {
      // Fetch tickets for pagination
      $data = $service->fetchTicketsForPagination(orderBy: $orderBy, sortBy: $sortBy, table: $table, limit: $limit, userId: $_SESSION['user_id']);

      // Count total tickets for pagination
      $totalItems = $service->countAllTicketsForPagination(allowedValues: $allowedValues, orderBy: $orderBy, sortBy: $sortBy, userId: $_SESSION['user_id']);
    }

    // Get pagination data
    $paginationData = $service->getPaginationData(limit: $limit, totalItems: $totalItems);

    return [
      "data"        => $data,
      "totalItems"  => $totalItems,
      "currentPage" => $paginationData['currentPage'],
      "totalPages"  => $paginationData['totalPages'],
      "pagination"  => $paginationData['pagination'],
      "statuses"    => $statuses,
      "priorities"  => $priorities,
      "departments" => $departments,
    ];
  }
}
