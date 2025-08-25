<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class LogoutController extends BaseController
{
    /**
     * Logs out the user and redirects to the specified URL.
     * 
     * @param string $url The URL to redirect to after logout.
     * @return void
     * @see logout() in helpers/functions.php
     */
    public function logout(string $url): void
    {
        requireLogin();

        if (isset($_POST['logout'])) {
            logout($url);
        } else {
            header("Location: index.php");
            die;
        }
    }
}
