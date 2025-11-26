<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        <p>Your ticket has been created in the system.</p>

                        <strong>Ticket ID:</strong> 
                        <p style='font-style: italic;'>{$ticketId}</p>
                        <strong>Title:</strong> 
                        <p style='font-style: italic;'>{$title}</p>
                        <strong>Description:</strong> 
                        <p style='font-style: italic;'>{$description}</p>

                        You can view the ticket here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;