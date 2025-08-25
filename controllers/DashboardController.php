<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'DashboardDataService.php';

class DashboardController extends BaseController
{
    private DashboardDataService $service;

    public function __construct()
    {
        $this->service = new DashboardDataService();
    }

    public function show(string $panel): void
    {
        $userId = trim($_SESSION["user_id"]);
        $dashboardData = $this->service->getDashboardData($panel, $userId);

        // Renders the dashboard view with the retrieved data
        $this->render("dashboard.php", ["panel" => $panel, "page" => "Dashboard", ...$dashboardData]);
    }
}
