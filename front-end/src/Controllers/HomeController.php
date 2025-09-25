<?php
namespace Controllers;

use Services\ApiService;
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
        $eventsResponse = $this->apiService->fetch('/events', 'GET');
        $events = $eventsResponse ?? [];

        $wishlistsResponse = $this->apiService->fetch('/events/wishlist?userId=' . $_SESSION['user']['id'], 'GET');
        $wishlist = $wishlistsResponse ?? [];

        $success = $_GET['success'] ?? null;
        $errors = $_GET['errors'] ?? null;

        echo $this->twig->render('app/home.twig', [
            'user' => $_SESSION['user'] ?? [],
            'events' => $events,
            'wishlist' => $wishlist,
            'success' => $success,
            'errors' => $errors
        ]);
    }
}
