<?php
require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_header.php';

$html .= "
                        We received a request to change the email address for your account in our application. <br>
                        The new email address that was requested is: <strong>{$newEmail}</strong>.<br>
                        If you initiated this change, no further action is required.<br>
                        If you <strong>did not</strong> request this change, please contact the administrator immediately at <strong>" . ADMIN_EMAIL . "</strong> or click the following link to cancel the change:
";

require ROOT . 'EmailTemplates' . DS . 'partials' . DS . '_email_footer.php';

return $html;