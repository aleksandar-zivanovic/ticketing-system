<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'controllers' . DS . 'TicketListingController.php';
require_once ROOT . 'services' . DS . 'DashboardDataService.php';

class DashboardController extends BaseController
{
    private DashboardDataService $dashboardDataService;
    private TicketListingController $ticketListingController;

    public function __construct()
    {
        $this->dashboardDataService = new DashboardDataService();
        $this->ticketListingController = new TicketListingController();
    }

    /**
     * Renders the dashboard view for the specified panel.
     * 
     * @param string $panel The dashboard panel to display (e.g., "admin", "user").
     * @return void
     * @see DashboardDataService::getDashboardData()
     * @see TicketListingController::getCurrentPage()
     */
    public function show(string $panel): void
    {
        $userId = trim($_SESSION["user_id"]);
        $currentPage = $this->ticketListingController->getCurrentPage();
        $dashboardData = $this->dashboardDataService->getDashboardData($panel, $currentPage, $userId);

        // Renders the dashboard view with the retrieved data
        $this->render("dashboard.php", ["panel" => $panel, "page" => "Dashboard", ...$dashboardData]);
    }
}
