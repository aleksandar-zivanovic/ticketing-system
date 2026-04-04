<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class PasswordForgotController extends BaseController
{
    // Controller methods for password forgot functionality


    public function show(): void
    {
        $this->render("forgot_password.php");
    }
}
