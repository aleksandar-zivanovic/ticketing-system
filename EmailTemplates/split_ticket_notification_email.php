<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        <p>Your ticket <span style='font-style: italic;'>\"{$title}\"</span> has been split into multiple tickets.</p>

                        <p>You can view the new tickets here:</p>
                        <ul>
";

foreach ($childTickets["child_tickets_ids"] as $key => $id) {
    $url = BASE_URL . "user/user-view-ticket.php?ticket=" . $id;
    $html .= "
        <li>
            <p>
                <a href=\"" . $url . "\"><strong>" . $childTickets["child_tickets_titles"][$key] . "</strong></a><br>
                <a href=\"" . $url . "\"><span style='font-style: italic;'>" . $url . "</span></a>
            </p>
        </li>
    ";
}

$html .= "
                        </ul>

                        You can view the original ticket here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
