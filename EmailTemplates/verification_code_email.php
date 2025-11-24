<?php
return "
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
                               align-items: center;
                               margin-bottom: 30px;'>
                        Verify Your Email Address
                    </h2>
                    <p class='data'
                        style='text-align: justify-all;
                              align-items: center; 
                              font-size: 16px;
                              padding-bottom: 16px;'>
                        <span style='font-weight: 400; display:inline-block; margin-bottom:10px;'>Hello {$name} {$surname}, </span><br>
                        Click the button below to verify your email address and activate your account.
                    </p>

                    <p>
                        <a href='{$verificationUrl}?email=$email&verification_code={$verificationCode}'
                            style='text-decoration: none; 
                                  color:white; 
                                  border: 2px solid black; 
                                  padding: 10px 30px;
                                  font-weight: bold;
                                  background-color: rgb(37, 99, 235, 1);
                                  text-align: center;'>
                            Click Here to Verify Email
                        </a>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
";
