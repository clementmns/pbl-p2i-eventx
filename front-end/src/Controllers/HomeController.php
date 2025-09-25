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
    private $sessionManager;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Templates');
        $this->twig = new Environment($loader);
        $this->apiService = new ApiService();
        $this->sessionManager = new SessionManager();
        $this->sessionManager->start();
    }

    public function index()
    {
        if (!$this->sessionManager->get('user')) {
            header('Location: /login');
            exit;
        }
        echo $this->twig->render('app/home.twig');
    }
}
