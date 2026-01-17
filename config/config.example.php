<?php
const DS = DIRECTORY_SEPARATOR;
define("ROOT", realpath(dirname(__FILE__)) . DS . ".." . DS);

// Configure the application base URL (e.g., "https://google.com/").
define('BASE_URL', 'https://example.com/');

// Set actual application domain (e.g., "google.com").
define("APP_DOMAIN", "example.com");

// Administrator email address for receiving critical notifications (e.g. "admin@example.com").
define('ADMIN_EMAIL', 'admin@example.com');

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

/*
 * USER_ROLES array defines the mapping of user roles to their corresponding integer values in the database.
 * This configuration allows for easy reference and modification of user roles within the application.
 * 
 * The array assigned to the `USER_ROLES` constant is just an example. It can be modified
 * to include different user roles and their associated integer values according to the needs of the
 * application and how the roles are stored in the database.
 * 
 * Format:
 *  - Key: The user role (e.g., "user", "admin")
 *  - Value: The integer value representing the role in the database (e.g., 1, 3)
 * 
 * Example:
 *  - "superadmin" => 4
 * 
 * If new roles are added to the database, the corresponding values should be updated here.
 */
define("USER_ROLES", [
    "user"       => 1,
    "moderator"  => 2,
    "admin"      => 3,
    "unverified" => 4,
    "blocked"    => 5,
]);

/*
 * DEPARTMENTS array defines the mapping of departments to their corresponding integer values in the database.
 * This configuration allows for easy reference and modification of departments within the application.
 * 
 * The array assigned to the `DEPARTMENTS` constant is just an example. It can be modified
 * to include different departments and their associated integer values according to the needs of the
 * application and how the departments are stored in the database.
 * 
 * Format:
 *  - Key: The department name (e.g., "Human Resources", "Finance")
 *  - Value: The integer value representing the department in the database (e.g., 2, 3)
 *
 * Example:
 *  - "General Management" => 2
 * 
 * If new departments are added to the database, the corresponding values should be updated here.
 */
define("DEPARTMENTS", [
    "Unassigned"             => 1,
    "Human Resources"        => 2,
    "Finance"                => 3,
    "Operations"             => 4,
    "Marketing"              => 5,
    "Information Technology" => 6,
]);

/*
 * Error page configuration
 * 
 * This constant defines the path to the error page used in the application.
 * It allows for easy modification of the error page location if needed.
 */
define('ERROR_PAGE', 'error.php');
