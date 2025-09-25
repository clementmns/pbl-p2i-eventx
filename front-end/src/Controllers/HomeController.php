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

        $joinedEventsResponse = $this->apiService->fetch('/events/user/' . $_SESSION['user']['id'], 'GET');
        $joinedEvents = $joinedEventsResponse ?? [];

        $success = $_GET['success'] ?? null;
        $errors = $_GET['errors'] ?? null;

        $events = array_filter($events, function ($event) use ($wishlist, $joinedEvents) {
            foreach ($wishlist as $wEvent) {
                if ($wEvent['id'] === $event['id']) {
                    return false;
                }
            }
            foreach ($joinedEvents as $jEvent) {
                if ($jEvent['id'] === $event['id']) {
                    return false;
                }
            }
            return true;
        });

        echo $this->twig->render('app/home.twig', [
            'user' => $_SESSION['user'] ?? [],
            'events' => $events,
            'wishlist' => $wishlist,
            'joinedEvents' => $joinedEvents,
            'success' => $success,
            'errors' => $errors
        ]);
    }
}
