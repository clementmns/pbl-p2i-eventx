<?php
namespace Controllers;

use Services\ApiService;
use Services\SessionManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class HomeController
{
    private $twig;
    private $apiService;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Templates');
        $this->twig = new Environment($loader);
        $this->apiService = new ApiService();
    }

    public function index()
    {
        echo $this->twig->render('app/home.twig');
    }
}
