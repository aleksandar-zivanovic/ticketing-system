<?php

/**
 * Formats monthly ticket counts for Chart.js.
 * 
 * @param array $created Monthly counts for created tickets.
 *   Format: [
 *     "Jan" => ["created_date" => int],
 *     "Feb" => ["created_date" => int],
 *     // ... all months
 *   ]
 * @param array $solved Monthly counts for solved tickets.
 *   Format: [
 *     "Jan" => ["closed_date" => int],
 *     "Feb" => ["closed_date" => int],
 *     // ... all months
 *   ]
 * 
 * @return array Returns an array with:
 *               - 'opened': array of integers, counts per month
 *               - 'closed': array of integers, counts per month
 */
function formatMonthlyDataForChartjs(array $created, array $solved): array
{
    $rawData   = [$created, $solved];
    $opened    = [];
    $closed    = [];
    for ($i = 0; $i < count($rawData); $i++) {
        foreach ($rawData[$i] as $month => $filteredMonthlyTickets) {
            foreach ($filteredMonthlyTickets as $filter => $numberOfTickets) {
                if ($filter === "created_date") {
                    $opened[] = $numberOfTickets;
                } elseif ($filter === "closed_date") {
                    $closed[] = $numberOfTickets;
                }
            }
        }
    }

    return ["opened" => $opened, "closed" => $closed];
}

/**
 * Prepares data for pie and bar charts.
 * 
 * @param array $ticketsPerFilters Array got by Ticket::countDataForDashboardTable.
 *  $ticketsPerFilters array is in the following format: 
 *      [
 *          [string, int], [string, int], [string, int], ...
 *      ] 
 * 
 * @return array Array in format: 
 * [
 *   "datasets" => [
 *     [
 *       "label" => [string],
 *       "data"  => [int, int, ...],
 *     ],
 *   ],
 *   "labels" => [
 *     string,
 *     string,
 *     ...,
 *   ],
 * ]
 * 
 */
function preparePieBarChartData(array $ticketsPerFilters): array
{
    $chartData["datasets"][0]["label"][] = "Total";
    foreach ($ticketsPerFilters as $ticketsPerFIlter) {
        $chartData["labels"][] = $ticketsPerFIlter[0];
        $chartData["datasets"][0]["data"][] = $ticketsPerFIlter[1];
    }

    return $chartData;
}

/**
 * Prepares pie/bar chart and table data grouped by filter and user (e.g. status + handled_by).
 * 
 * Uses global $panel ("admin" or "user").
 * 
 * @param array $allTicketsData Array of all tickets data got by `fetchAllTickets` method.
 * @param array $allDataForTheClass Array returned by child getAll methods like getAllPriorities or getAllDepartments.
 * @param string $filter A ticket key used for filtering data (e.g. "created_by", "handled_by", ...).
 * 
 * @return array
 */
function prepareChartAndTableDataByFilterAndUser(array $allTicketsData, array $allDataForTheClass, string $filter): array
{
    // Builds array with user IDs as keys and their ticket IDs as values
    $ticketsPerClass = [];
    foreach ($allTicketsData as $singleTicket) {
        if (!in_array($singleTicket[$filter], $ticketsPerClass)) {
            $ticketsPerClass[$singleTicket[$filter]][] = $singleTicket["id"];
        }
    }

    // Sorts tickets count by filter in descending order
    arsort($ticketsPerClass);

    global $panel;
    $totalTickets = count($allTicketsData);
    $countsForTable = [];
    $chartData = [];
    $chartData["datasets"][0]["label"][] = "Total";
    foreach ($ticketsPerClass as $userId => $ticketId) {
        $totalTicketsPerClass = count($ticketId);
        foreach ($allDataForTheClass as $user) {
            if ($userId === $user["id"]) {
                $countsForTable[] = [
                    "ID: {$user["id"]} | Name: {$user["name"]} {$user["surname"]}",
                    $filter === "created_by"
                        ? "<a href='#'>{$totalTicketsPerClass}</a>"
                        : $totalTicketsPerClass 
                    , 
                    countPercentage($totalTicketsPerClass, $totalTickets)
                ];
                $chartData["labels"][] = [
                    $panel === "admin"
                        ? "ID: {$user["id"]} | Name: {$user["name"]} {$user["surname"]}"
                        : "Name: {$user["name"]} {$user["surname"]}"
                ];
                $chartData["datasets"][0]["data"][] = count($ticketId);
                break;
            }
        }
    }

    return [
        "table_data" => $countsForTable,
        "chart_data" => $chartData,
    ];
}
