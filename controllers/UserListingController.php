<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'UserListingService.php';
require_once ROOT . 'traits' . DS . 'PaginationTrait.php';

class UserListingController extends BaseController
{
    use PaginationTrait;

    private UserListingService $service;

    public function __construct()
    {
        $this->service = new UserListingService();
    }

    public function show(): void
    {
        try {
            $data["orderBy"]      = $this->validateOrderByRequest();
            $validateLimitRequest = $this->validateLimitRequest();
            $data["limit"]        = $validateLimitRequest["limit"];
            $data["options"]      = $validateLimitRequest["options"];
            $data["currentPage"]  = $this->getCurrentPage();
            $preparedData         = $this->service->preparedData($data["limit"], $data["currentPage"], $data["orderBy"]);

            $data += $preparedData;

            // Show the user listing view
            $this->render("users_listing.php", $data);
        } catch (\Throwable $th) {
            redirectAndDie(BASE_URL . " error.php", $th->getMessage() . " | " . $th->getFile() . " | " . $th->getLine(), "fail");
        }
    }
}
