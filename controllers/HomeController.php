<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class HomeController extends BaseController
{
    public function show()
    {
        $this->render("home.php");
    }
}