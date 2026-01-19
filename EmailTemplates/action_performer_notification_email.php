<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$actionPreposition = $performedBy["action"] === "reset password" ? "to" : "for";

$html .= "
                        <p>You {$performedBy["action"]} {$actionPreposition} user{$plural} with ID{$plural}: <span style='font-style: italic; font-weight:bold;'>{$idsString}</span> at {$timestamp}.</p>

                        You can view the users listing admin panel here:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
