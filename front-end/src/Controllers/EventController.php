<?php
namespace Controllers;

use Services\ApiService;
use Services\SessionManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class EventController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Templates');
        $this->twig = new Environment($loader);
    }

    public function createEventView()
    {
        echo $this->twig->render('app/createEvent.twig', [
            'profile' => $_SESSION['profile'] ?? []
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

        $apiService = new ApiService();
        $response = $apiService->fetch('/events', 'POST', $eventData);

        if (!$response) {
            $errorMsg = 'Unable to connect to event service. Please try again later.';
            echo $this->twig->render('app/createEvent.twig', [
                'errors' => [$errorMsg]
            ]);
            return;
        }

        if (isset($response['error'])) {
            $errorMsg = $response['error'];
            echo $this->twig->render('app/createEvent.twig', [
                'errors' => [$errorMsg]
            ]);
            return;
        }

        header('Location: /?success=Event created successfully!');
    }

    public function editEventView()
    {
        $eventId = $_GET['eventId'] ?? null;
        if (!$eventId) {
            $errorMsg = 'Event ID is required.';
            echo $this->twig->render('app/editEvent.twig', [
                'errors' => [$errorMsg]
            ]);
            return;
        }

        $apiService = new ApiService();
        $event = $apiService->fetch("/events/{$eventId}", 'GET');

        if (!$event) {
            $errorMsg = 'Unable to fetch event details.';
            echo $this->twig->render('app/editEvent.twig', [
                'errors' => [$errorMsg]
            ]);
            return;
        }

        echo $this->twig->render('app/editEvent.twig', [
            'event' => $event
        ]);
    }

    public function editEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        $eventData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'startDate' => $_POST['startDate'] ?? '',
            'endDate' => $_POST['endDate'] ?? '',
            'place' => $_POST['location'] ?? ''
        ];

        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}", 'PUT', $eventData);

        if (!$response) {
            $errorMsg = 'Unable to update event. Please try again later.';
            echo $this->twig->render('app/editEvent.twig', [
                'errors' => [$errorMsg],
                'event' => array_merge(['id' => $eventId], $eventData)
            ]);
            return;
        }

        if (isset($response['error'])) {
            $errorMsg = $response['error'];
            echo $this->twig->render('app/editEvent.twig', [
                'errors' => [$errorMsg],
                'event' => array_merge(['id' => $eventId], $eventData)
            ]);
            return;
        }

        header('Location: /?success=Event updated successfully!');
    }

    public function deleteEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        if (!$eventId) {
            header('Location: /?errors=Unable to delete event. Please try again later.');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}", 'DELETE');
        if (!$response) {
            header('Location: /?errors=Unable to delete event. Please try again later.');
            return;
        }
        if (isset($response['error'])) {
            echo $this->twig->render('app/home.twig', [
                'errors' => [$response['error']]
            ]);
            return;
        }
        header('Location: /?success=Event deleted successfully!');
    }

        // Join an event
    public function joinEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            header('Location: /?errors=Unable to join event.');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/join", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            header('Location: /?errors=Unable to join event.');
            return;
        }
        header('Location: /?success=Joined event successfully!');
    }

        // Quit an event
    public function quitEvent()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            header('Location: /?errors=Unable to quit event.');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/quit", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            header('Location: /?errors=Unable to quit event.');
            return;
        }
        header('Location: /?success=Quit event successfully!');
    }

        // Add event to wishlist
    public function addToWishlist()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            header('Location: /?errors=Unable to add to wishlist.');
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/wishlist/add", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            header('Location: /?errors=Unable to add to wishlist.');
            return;
        }
        header('Location: /?success=Added to wishlist!');
    }

        // Remove event from wishlist
    public function removeFromWishlist()
    {
        $eventId = $_POST['eventId'] ?? null;
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$eventId || !$userId) {
            echo $this->twig->render('app/home.twig', [
                'errors' => ['Event ID and User ID are required.']
            ]);
            return;
        }
        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}/wishlist/remove", 'POST', [
            'userId' => $userId
        ]);
        if (!$response || isset($response['error'])) {
            echo $this->twig->render('app/home.twig', [
                'errors' => [$response['error'] ?? 'Unable to remove from wishlist.']
            ]);
            return;
        }
        header('Location: /?success=Removed from wishlist!');
    }
}
