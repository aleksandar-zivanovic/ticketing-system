<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'TicketListingService.php';
require_once ROOT . 'services' . DS . 'SortingAndOrderingService.php';
require_once ROOT . 'services' . DS . 'TicketReferenceService.php';
require_once ROOT . 'traits' . DS . 'PaginationTrait.php';

class TicketListingController extends BaseController
{
  private TicketListingService $ticketListingService;
  private SortingAndOrderingService $sortingService;
  private TicketReferenceService $ticketReferenceService;
  use PaginationTrait;

  public function __construct()
  {
    $this->ticketListingService   = new TicketListingService();
    $this->sortingService         = new SortingAndOrderingService();
    $this->ticketReferenceService = new TicketReferenceService();
  }

  /**
   * Prepares tickets listing data for the partial template.
   *
   * - Fetches tickets based on panel type (admin/user) and optional "handled by me" filter.
   * - Applies sorting, filtering, and pagination.
   * - Loads supporting data for dropdown filters: statuses, priorities, and departments.
   *
   * @param string $action Action type for the listing (e.g., "all", "my", "handling")
   * @param string $panel "admin" or "user" – determines ticket scope and links
   * @param string $sortBy Filter to sort by (status, priority, department, etc.)
   * @param string $orderBy "ASC" | "DESC" or "newest" | "oldest"
   * @param string|null $table Column from tickets table
   * @param int $limit Number of tickets per page
   * @param array $options Additional options for pagination
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
   * @throws InvalidArgumentException If user ID in session is invalid.
   * @see TicketListingService::prepareTicketsListingData()
   */
  public function prepareTicketsListingData(string $action, string $panel, ?string $sortBy, string $orderBy, ?string $table, int $limit, array $options): array
  {
    $userId = trim($_SESSION["user_id"]);
    $userId = $this->validateId($userId);
    if ($userId === false) {
      throw new InvalidArgumentException("Invalid user ID in session.");
    }

    $currentPage = $this->getCurrentPage();

    return $this->ticketListingService->prepareTicketsListingData($action, $panel, $sortBy, $orderBy, $table, $limit, $userId, $currentPage, $options);
  }

  public function show(string $panel, string $action, string $fileName): void
  {
    try {
      $allowedValues        = $this->ticketReferenceService->getReferenceData();
      $sortByValidation     = $this->sortingService->validateSortByRequest($allowedValues);
      $sortBy               = $sortByValidation["cleanSortBy"];
      $table                = $sortByValidation["table"];
      $orderBy              = $this->validateOrderByRequest();
      $validateLimitRequest = $this->validateLimitRequest();
      $limit                = $validateLimitRequest["limit"];
      $options              = $validateLimitRequest["options"];
      $data = $this->prepareTicketsListingData($action, $panel, $sortBy, $orderBy, $table, $limit, $options);
      $data["fileName"]     = $fileName;
      $data["limit"]        = $limit;
      $data["action"]       = $action;

      $this->render("ticket_listing.php", $data);
    } catch (\Throwable $th) {
      redirectAndDie("index.php", $th->getMessage(), "fail");
    }
  }
}
