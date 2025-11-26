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
        width='100%' bgcolor='white' style='border:2px solid black'>
        <tbody>
            <tr>
                <td align='center'>
                    <table align='center' border='0' cellpadding='0'
                        cellspacing='0' class='col-550' width='100%'>
                        <tbody>
                            <tr>
                                <td align='center' style='background-color: rgba(31,41,55,1);
                                           height: 50px;'>

                                    <a href='{$siteUrl}' style='text-decoration: none;'>
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
                        {$subject}
                    </h2>
                    <div class='data'
                        style='text-align: justify-all;
                        align-items: center; 
                        font-size: 15px;
                        padding-bottom: 16px;'>
                        <span style='font-weight: 400; display:inline-block; margin-bottom:10px;'>
                            Dear {$name} {$surname},
                        </span><br>

                        <p>Your ticket <span style='font-style: italic;'>\"{$title}\"</span> with ID: <span style='font-style: italic; font-weight:bold;'>{$ticketId}</span> is assigned to an administrator.</p>

                        You can view the ticket here:
                        
                        <p>
                            <a href='{$this->siteUrl}user/user-view-ticket.php?ticket={$ticketId}'
                                style='display: inline-block; 
                                    text-decoration: none; 
                                    color: white; 
                                    border: 2px solid black; 
                                    margin-bottom: 30px;
                                    padding: 10px 30px;
                                    font-weight: bold;
                                    background-color: rgb(37, 99, 235, 1);
                                    text-align: center;'>
                                Click Here to View Your Ticket
                            </a>
                        </p>

                        Thank you,<br>
                        The Ticketing System Team
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
";

return $html;
