<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';

class PageController extends BaseController
{
    /**
     * Renders a page based on the provided page name.
     *
     * @param string $page The name of the page to render.
     * @return void
     * @see BaseController::render()
     */
    public function show(string $page): void
    {
        $this->render($page);
    }
}