<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'TicketShowService.php';

class TicketShowController extends BaseController
{
    private TicketShowService $service;

    public function __construct()
    {
        $this->service = new TicketShowService();
    }

    public function validateRequest(): array
    {
        if (!$this->hasValue($_GET['ticket'])) {
            return ["success" => false, "message" => "Ticket ID is required."];
        }

        $data["id"] = $this->validateId($_GET['ticket']);
        if ($data["id"] === false) {
            return ["success" => false, "message" => "Invalid Ticket ID."];
        }

        return $this->service->validate($data);
    }

    public function show(string $view, array|string $data = []): void
    {
        $this->redirectUrl = "/ticketing-system/admin/admin-ticket-listing.php";
        $validation = $this->validateRequest();

        $validation["data"]["panel"] = $data;
        $this->handleValidation($validation);
        $this->render($view, $validation["data"]);
    }
}
