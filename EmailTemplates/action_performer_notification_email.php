<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        <p>You {$performedBy["action"]} for user{$plural} with ID{$plural}: <span style='font-style: italic; font-weight:bold;'>{$idsString}</span> at {$timestamp}.</p>

                        You can view the users listing admin panel here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
