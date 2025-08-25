<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'TicketListingService.php';

class TicketListingController extends BaseController
{
  private TicketListingService $service;

  public function __construct()
  {
    $this->service = new TicketListingService();
  }

  /**
   * Validates the 'sort' parameter from the GET request.
   * 
   * @return array{table: string|null, cleanSortBy: string|null}
   *         - 'table': The corresponding table for the sortBy value or null if not applicable.
   *         - 'cleanSortBy': The sanitized sortBy value or null if not provided.
   * 
   * @see TicketListingService::validateSortBy()
   */
  public function validateSortByRequest(): array
  {
    if (isset($_GET['sort']) && !empty(trim($_GET['sort']))) {

      // Trims and sanatizates
      $sortBy = cleanString($_GET['sort']);

      return $this->service->validateSortBy($sortBy);
    } else {
      return ["table" => null, "cleanSortBy" => null];
    }
  }

  /**
   * Validates the 'limit' parameter from the GET request and manages session storage.
   * Limit parameter controls the number of results per page for ticket listings.
   * 
   * @return int The validated limit value. Returns 0 for "all" or if no limit is set, otherwise returns a positive integer.
   */
  public function validateLimitRequest(): array
  {
    // Set results per page
    if (isset($_GET["limit"])) {
      if ($_GET["limit"] !== "all") {
        $limit = $this->validateId($_GET["limit"]);
        $_SESSION["limit"] = $limit = $limit === false ? 0 : $limit;
      } else {
        $limit = $_SESSION["limit"] = 0;
      }
    } elseif (!isset($_GET["limit"]) && isset($_SESSION["limit"])) {
      $limit = (int)trim($_SESSION["limit"]);
    } else {
      $limit = 10;
    }

    $preparedLimit = $this->service->prepareLimitOptions($limit);

    $_SESSION["limit"] = $preparedLimit["limit"];

    return ["limit" => $preparedLimit["limit"], "options" => $preparedLimit["options"]];
  }

  /**
   * Validates the 'order_by' parameter from the GET request.
   * 
   * @return string "oldest" if 'order_by' is set to "oldest", otherwise "newest".
   */
  public function validateOrderByRequest(): string
  {
    return (isset($_GET["order_by"]) && trim($_GET["order_by"]) === "oldest") ?  "oldest" : "newest";
  }

  /**
   * Gets current page from query string parameter "page".
   * 
   * @return int Returns current page if page parameter from query strng is set and valid, otherwise 1.
   */
  public function getCurrentPage(): int
  {
    if (isset($_GET["page"])) {
      $currentPage = $this->validateId($_GET["page"]);
      if ($currentPage === false || $currentPage < 1) {
        $currentPage = 1;
      }
    }

    return $currentPage ?? 1;
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

    return $this->service->prepareTicketsListingData($action, $panel, $sortBy, $orderBy, $table, $limit, $userId, $currentPage, $options);
  }

  public function show(string $panel, string $action, string $fileName): void
  {
    try {
      $validateLimitRequest = $this->validateLimitRequest();
      $sortByValidation = $this->validateSortByRequest();
      $sortBy           = $sortByValidation["cleanSortBy"];
      $table            = $sortByValidation["table"];
      $orderBy          = $this->validateOrderByRequest();
      $limit            = $validateLimitRequest["limit"];
      $options          = $validateLimitRequest["options"];
      $data = $this->prepareTicketsListingData($action, $panel, $sortBy, $orderBy, $table, $limit, $options);
      $data["fileName"] = $fileName;
      $data["limit"]    = $limit;
      $data["action"]   = $action;

      $this->render("ticket_listing.php", $data);
    } catch (\Throwable $th) {
      redirectAndDie("index.php", $th->getMessage(), "fail");
    }
  }
}
