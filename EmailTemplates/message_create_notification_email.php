<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        <p>There is a new message in ticket <span style='font-style: italic;'>\"{$title}\"</span> with ID: <span style='font-style: italic; font-weight:bold;'>{$ticketId}</span>.</p>

                        <strong>Ticket Title:</strong> 
                        <p style='font-style: italic;'>{$title}</p>
                        <strong>Date:</strong> 
                        <p style='font-style: italic;'>{$date}</p>
                        <strong>Sender:</strong> 
                        <p style='font-style: italic;'>{$messageCreatorFullName}</p><br><br>

                        <strong>Message Content:</strong>
                        <p style='font-style: italic;'>{$messageContent}</p>

                        You can view the ticket here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
