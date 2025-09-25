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
        $flash = $this->sessionManager->getFlash();
        $user = $this->sessionManager->get('user');

        if (!$user || !isset($user['id'])) {
            $this->sessionManager->setFlash('error', 'Please login to access this page');
            header('Location: /login');
            return;
        }

        $eventsResponse = $this->apiService->fetch('/events', 'GET');
        if (!$eventsResponse) {
            $this->sessionManager->setFlash('error', 'Unable to fetch events. Please try again later.');
            $events = [];
        } else {
            $events = $eventsResponse;
        }

        $wishlistsResponse = $this->apiService->fetch('/events/wishlist?userId=' . $user['id'], 'GET');
        $wishlist = $wishlistsResponse ?? [];

        $joinedEventsResponse = $this->apiService->fetch('/events/user/' . $user['id'], 'GET');
        $joinedEvents = $joinedEventsResponse ?? [];

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
            'flash' => $flash
        ]);
    }
}
