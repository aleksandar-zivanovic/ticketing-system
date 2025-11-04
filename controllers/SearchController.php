<?php
require_once __DIR__ . "/../config/config.php";
require_once ROOT . "controllers" . DS . "BaseController.php";
require_once ROOT . "services" . DS . "SearchService.php";

class SearchController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new SearchService();
    }

    public function validateRequest(): array
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method."];
        }

        if ($this->hasValue($_POST["searchInput"]) === false) {
            return ["success" => false, "message" => "Search field cannot be empty."];
        }

        if ($this->hasValue($_POST["searchOption"]) === false) {
            return ["success" => false, "message" => "Search dropdown must have a value."];
        }

        $data["searchOption"] = cleanString($_POST["searchOption"]);

        if (in_array($data["searchOption"], ["TicketID", "UserID"])) {
            $data["searchInput"] = $this->validateId($_POST["searchInput"]);
            if ($data["searchInput"] === false) {
                return ["success" => false, "message" => "Invalid ID format."];
            }
        } else {
            $data["searchInput"] = cleanString($_POST["searchInput"]);
        }

        return $this->service->validate($data);
    }

    public function showResults()
    {
        try {
            $validationData = $this->validateRequest();
            if ($validationData["success"] === false) {
                throw new Exception($validationData["message"]);
            }

            // If validation passes, proceed with search
            $data = $this->service->performSearch($validationData["data"]["searchOption"], $validationData["data"]["searchInput"]);

            // Render results
            ob_start();
            include ROOT . "views" . DS . "partials" . DS . "_search_results.php";
            $html = ob_get_clean();

            echo $html;
            die;
        } catch (\Throwable $th) {
            echo "<p class='text-red-600 p-4 italic'>Error: " . $th->getMessage() . "</p>";
            die;
        }
    }
}
