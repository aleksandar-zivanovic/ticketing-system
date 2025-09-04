<?php
const DS = DIRECTORY_SEPARATOR;
define("ROOT", realpath(dirname(__FILE__)) . DS . ".." . DS);

// Set actual application domain (e.g., "google.com").
define("APP_DOMAIN", "example.com");

// Set highest ticket priority level.
// Set this to a priority level that exists in your database.
define("HIGHEST_PRIORITY", "your_highest_priority_value");

/* 
 * General configuration for the ticketing system
 * This file contains various settings related to the ticketing system's behavior.
 * 
 * Ticket Statuses and Styles Configuration
 * 
 * This section defines the mapping of ticket statuses to their corresponding CSS styles.
 * Users can modify this array to add new statuses and their associated styles.
 * 
 * The array assigned to the `TICKET_STATUSES` constant is just an example. It can be modified
 * to include different ticket statuses and corresponding styles according to the needs of the
 * application and how the statuses are stored in the database.
 * 
 * Format:
 *  - Key: The ticket status (e.g., "waiting", "in progress")
 *  - Value: The CSS style applied to the status (e.g., "color:coral;")
 * 
 * Example:
 *  - "new_status" => "color:yellow; font-weight:bold;"
 * 
 * If new statuses are added to the database, the corresponding styles should be updated here.
 */

define("TICKET_STATUSES", [
    "waiting"     => "color:coral;",                     // Waiting status is displayed in coral
    "in progress" => "color:coral; color:deepskyblue;",  // In progress status is displayed in deepskyblue
    "closed"      => "color:green; font-style: italic;", // Closed status is displayed as green and italic
]);

// Session refresh interval in seconds.
// Defines how often user session data is revalidated against the database.
define('SESSION_REFRESH_INTERVAL', 300); // 5 minutes