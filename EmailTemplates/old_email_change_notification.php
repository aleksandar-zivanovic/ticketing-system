<?php
$html = "
<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Account Verification Email</title>
</head>
<!-- Complete Email template -->

<body style='background-color:grey;'>
    <table align='center' border='0' cellpadding='0' cellspacing='0'
        width='550' bgcolor='white' style='border:2px solid black'>
        <tbody>
            <tr>
                <td align='center'>
                    <table align='center' border='0' cellpadding='0'
                        cellspacing='0' class='col-550' width='550'>
                        <tbody>
                            <tr>
                                <td align='center' style='background-color: rgba(31,41,55,1);
                                           height: 50px;'>

                                    <a href='http://localhost/ticketing-system/' style='text-decoration: none;'>
                                        <p style='color:white;
                                                  font-weight:bold;'>
                                            Ticketing System
                                        </p>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr style='display: inline-block;'>
                <td style='height: 150px;
                    padding: 20px;
                    border: none; 
                    border-bottom: 2px solid #361B0E;
                    background-color: white;'>

                    <h2 style='text-align: left;
                        align-items: center;'>
                        Email Change Notification
                    </h2>
                    <p class='data'
                        style='text-align: justify-all;
                        align-items: center; 
                        font-size: 15px;
                        padding-bottom: 16px;'>
                        <span style='font-weight: 400; display:inline-block; margin-bottom:10px;'>
                            Dear {$name} {$surname},
                        </span><br>
                        We received a request to change the email address for your account in our application. <br>
                        The new email address that was requested is: <strong>{$newEmail}</strong>.<br>
                        If you initiated this change, no further action is required.<br>
                        If you <strong>did not</strong> request this change, please contact the administrator immediately at <strong>" . ADMIN_EMAIL . "</strong> or click the following link to cancel the change:
                        <p>
                            <a href='{$rollbackLink}'
                                style='display: inline-block; 
                                    text-decoration: none; 
                                    color: white; 
                                    border: 2px solid black; 
                                    margin-bottom: 30px;
                                    padding: 10px 30px;
                                    font-weight: bold;
                                    background-color: rgb(37, 99, 235, 1);
                                    text-align: center;'>
                                Click Here to Cancel Email Change
                            </a>
                        </p>

                        Thank you,
                        The Ticketing System Team
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
";

return $html;
