<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        <p>Your role has been changed to: <span style='font-style: italic; font-weight:bold;'>{$roleName}</span>.</p>

                        You can view the ticket here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
