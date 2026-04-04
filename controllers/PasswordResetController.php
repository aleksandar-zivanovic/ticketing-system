<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class PasswordResetController extends BaseController
{
    // Controller methods for password reset functionality


    public function show(): void
    {
        $this->render("reset_password.php");
    }

    public function resetPassword(): void
    {
        dd($_POST);
        dd("Reset password action executed.");
    }
}
