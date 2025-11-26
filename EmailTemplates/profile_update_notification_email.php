<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                    <p>Your {$change} has been successfully updated.<br>
                        If you did not make this change, please contact our support team immediately.<br>
                        Click the button below to visit your profile page.
                    </p>
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;