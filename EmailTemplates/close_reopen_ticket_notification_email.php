<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        <p>Your ticket <span style='font-style: italic;'>\"{$title}\"</span> with ID: <span style='font-style: italic; font-weight:bold;'>{$ticketId}</span> is <span style='font-style: italic; font-weight:bold;'>{$actionPastTense}</span>.</p>

                        You can view the ticket here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
