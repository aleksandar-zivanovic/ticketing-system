<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                    Click the button below to verify your email address and activate your account.
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;
