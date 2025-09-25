<?php

namespace Controllers;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ErrorController
{

    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Templates');
        $this->twig = new Environment($loader);
    }
    public function notFound()
    {
        http_response_code(404);
        echo $this->twig->render('404.twig');
    }
}
