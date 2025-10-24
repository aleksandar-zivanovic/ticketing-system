<?php

return [
    "/index.php"                   => [
        "handler" => [HomeController::class, "show"],
        "params"  => ["home.php"]
    ],
    "/admin-ticket-listing"         => [
        "handler" => [TicketListingController::class, "show"],
        "params"  => ["admin", "all", "admin-ticket-listing.php"]
    ],
    "/admin-tickets-i-handle"       => [
        "handler" => [TicketListingController::class, "show"],
        "params"  => ["admin", "handling", "admin-tickets-i-handle.php"]
    ],
    "/admin/user-tickets-list"      => [
        "handler" => [TicketListingController::class, "show"],
        "params"  => ["admin", "users-tickets", "ticket.php",]
    ],
    "/user-ticket-listing"          => [
        "handler" => [TicketListingController::class, "show"],
        "params"  => ["user", "my", "user-ticket-listing.php"]
    ],
    "/admin/view-ticket"            => [
        "handler" => [TicketShowController::class, "show"],
        "params"  => ["ticket.php", "admin"]
    ],
    "/user/user-view-ticket"        => [
        "handler" => [TicketShowController::class, "show"],
        "params"  => ["ticket.php", "user"]
    ],
    "/admin/users-listing"          => [
        "handler" => [UserListingController::class, "show"],
    ],
    "/admin/split-ticket"           => [
        "handler" => [TicketSplitController::class, "show"],
    ],
    "/split_ticket_action.php"      => [
        "handler" => [TicketSplitController::class, "splitTicket"],
    ],
    "/create-ticket.php"            => [
        "handler" => [TicketController::class, "show"],
    ],
    "/create_ticket_action.php"     => [
        "handler" => [TicketController::class, "createTicket"],
    ],
    "/ticket_close_action.php"      => [
        "handler" => [TicketCloseReopenController::class, "closeReopenTicket"],
    ],
    "/ticket_reopen_action.php"     => [
        "handler" => [TicketCloseReopenController::class, "closeReopenTicket"],
    ],
    "/ticket_delete_action.php"     => [
        "handler" => [TicketController::class, "deleteTicket"],
    ],
    "/take_ticket_action.php"       => [
        "handler" => [TicketTakeController::class, "takeTicket"],
    ],
    "/admin-edit-message.php"       => [
        "handler" => [MessageController::class, "show"],
    ],
    "/user-edit-message.php"        => [
        "handler" => [MessageController::class, "show"],
    ],
    "/message_action.php"           => [
        "handler" => [MessageController::class, "execute"],
    ],
    "/profile.php"                  => [
        "handler" => [ProfileController::class, "show"],
    ],
    "/profile_update_action.php"    => [
        "handler" => [ProfileController::class, "update"],
    ],
    "/admin-dashboard.php"                => [
        "handler" => [DashboardController::class, "show"],
        "params"  => ["admin"]
    ],
    "/my-dashboard.php"             => [
        "handler" => [DashboardController::class, "show"],
        "params"  => ["user"]
    ],
    "/login.php"                    => [
        "handler" => [LoginController::class, "show"],
        "params"  => ["login.php"]
    ],
    "/login_action.php"             => [
        "handler" => [LoginController::class, "login"],
        "params"  => ["login_action.php"]
    ],
    "/register.php"                 => [
        "handler" => [RegisterController::class, "show"],
        "params"  => ["register.php"]
    ],
    "/register_action.php"          => [
        "handler" => [RegisterController::class, "register"],
    ],
    "/logout.php"                   => [
        "handler" => [LogoutController::class, "logout"],
        "params"  => ["logout.php"]
    ],
    "/resend-code.php"              => [
        "handler" => [VerificationController::class, "show"],
        "params"  => ["resend_code.php"]
    ],
    "/resend_code_action.php"           => [
        "handler" => [VerificationController::class, "sendVerificationEmail"],
        "params"  => ["resend"]
    ],
    "/email-verification.php"        => [
        "handler" => [VerificationController::class, "verifyUser"]
    ],
    "/tests/",
];
