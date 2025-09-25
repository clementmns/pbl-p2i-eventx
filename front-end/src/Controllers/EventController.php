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
            $_SESSION['flash_error'] = 'Event ID is required.';
            header('Location: /');
            exit;
        }

        $apiService = new ApiService();
        $response = $apiService->fetch("/events/{$eventId}", 'DELETE');

        if (!$response) {
            $_SESSION['flash_error'] = 'Unable to delete event. Please try again later.';
            header('Location: /');
            exit;
        }

        if (isset($response['error'])) {
            $_SESSION['flash_error'] = $response['error'];
            header('Location: /');
            exit;
        }

        $_SESSION['flash_success'] = 'Event deleted successfully!';
        header('Location: /');
        exit;
    }
}
