<?php
$html = "
<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>{$subject}</title>
</head>

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

                                    <a href='{$this->siteUrl}' style='text-decoration: none;'>
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
";