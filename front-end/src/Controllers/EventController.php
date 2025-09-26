<?php
namespace Controllers;

use Services\ApiService;
use Services\SessionManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class EventController
{
    private $twig;
    private $sessionManager;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Templates');
        $this->twig = new Environment($loader);
        $this->sessionManager = new SessionManager();
        $this->sessionManager->start();
    }

    public function createEventView()
    {
        $flash = $this->sessionManager->getFlash();
        echo $this->twig->render('app/createEvent.twig', [
            'user' => $_SESSION['user'] ?? [],
            'flash' => $flash
        ]);
    }

    public function createEvent()
    {
        $eventData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'startDate' => $_POST['startDate'] ?? '',
            'endDate' => $_POST['endDate'] ?? '',
            'place' => $_POST['location'] ?? '',
            'userId' => $_SESSION['user']['id'] ?? null
        ];

        if ($eventData['startDate'] > $eventData['endDate']) {
            $this->sessionManager->setFlash('error', 'Start date cannot be after end date.');
            header('Location: /events/create');
            return;
        }

        $apiService = new ApiService();
        $response = $apiService->fetch('/events', 'POST', $eventData);

        if (!$response) {
            $this->sessionManager->setFlash('error', 'Unable to connect to event service. Please try again later.');
            header('Location: /events/create');
            return;
        }

        if (isset($response['error'])) {
            $this->sessionManager->setFlash('error', $response['error']);
            header('Location: /events/create');
            return;
        }

        header('Location: /');
    }

    public function editEventView()
    {
        $eventId = $_GET['eventId'] ?? null;
        if (!$eventId) {
            $this->sessionManager->setFlash('error', 'Event ID is required.');
            header('Location: /');
            return;
        }

        $apiService = new ApiService();
        $event = $apiService->fetch("/events/{$eventId}", 'GET');

        if (!$event) {
            $this->sessionManager->setFlash('error', 'Unable to fetch event details.');
            header('Location: /');
            return;
        }

        $flash = $this->sessionManager->getFlash();
        echo $this->twig->render('app/editEvent.twig', [
            'user' => $_SESSION['user'] ?? [],
            'event' => $event,
            'flash' => $flash
        ]);
    }

    public function editEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        if (!$eventId) {
            $this->sessionManager->setFlash('error', 'Event ID is required.');
            header('Location: /');
            return;
        }

        $eventData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'startDate' => $_POST['startDate'] ?? '',
            'endDate' => $_POST['endDate'] ?? '',
            'place' => $_POST['location'] ?? ''
        ];

        if ($eventData['startDate'] > $eventData['endDate']) {
            $this->sessionManager->setFlash('error', 'Start date cannot be after end date.');
            header('Location: /events/edit?eventId=' . $eventId);
            return;
        }

        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}", 'PUT', $eventData);

        if (!$response) {
            $this->sessionManager->setFlash('error', 'Unable to update event. Please try again later.');
            header('Location: /events/' . $eventId . '/edit');
            return;
        }

        if (isset($response['error'])) {
            $this->sessionManager->setFlash('error', $response['error']);
            header('Location: /events/' . $eventId . '/edit');
            return;
        }

        $this->sessionManager->setFlash('success', 'Event updated successfully!');
        header('Location: /');
    }

    public function deleteEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        if (!$eventId) {
            $this->sessionManager->setFlash('error', 'Unable to delete event. Please try again later.');
            header('Location: /');
            return;
        }

        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}", 'DELETE');
        if (!$response) {
            $this->sessionManager->setFlash('error', 'Unable to delete event');
            header('Location: /');
            return;
        }
        $this->sessionManager->setFlash('success', 'Event deleted successfully!');
        header('Location: /');
    }

    public function joinEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            $this->sessionManager->setFlash('error', 'Unable to join event.');
            header('Location: /');
            return;
        }

        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/join", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            $this->sessionManager->setFlash('error', 'Unable to join event.');
            header('Location: /');
            return;
        }
        $this->sessionManager->setFlash('success', 'Joined event successfully!');
        header('Location: /');
    }

    public function quitEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            $this->sessionManager->setFlash('error', 'Unable to quit event.');
            header('Location: /');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/quit", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            $this->sessionManager->setFlash('error', 'Unable to quit event.');
            header('Location: /');
            return;
        }
        $this->sessionManager->setFlash('success', 'Quit event successfully!');
        header('Location: /');
    }

    public function addToWishlist()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            $this->sessionManager->setFlash('error', 'Unable to add to wishlist.');
            header('Location: /');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/wishlist/add", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            $this->sessionManager->setFlash('error', 'Unable to add to wishlist.');
            header('Location: /');
            return;
        }
        $this->sessionManager->setFlash('success', 'Added to wishlist!');
        header('Location: /');
    }

    public function removeFromWishlist()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            $this->sessionManager->setFlash('error', 'Event ID and User ID are required.');
            header('Location: /');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/wishlist/remove", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            $this->sessionManager->setFlash('error', 'Unable to remove from wishlist.');
            header('Location: /');
            return;
        }
        $this->sessionManager->setFlash('success', 'Removed from wishlist!');
        header('Location: /');
    }
}
