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

    public function eventView()
    {
        $apiService = new ApiService();
        $eventsResponse = $apiService->fetch('/events', 'GET');
        $events = $eventsResponse['data'] ?? [];
        echo $this->twig->render('app/eventsList.twig', [
            'profile' => $_SESSION['profile'] ?? [],
            'events' => $events
        ]);
    }

    public function createEventView()
    {
        echo $this->twig->render('app/createEvent.twig', [
            'profile' => $_SESSION['profile'] ?? []
        ]);
    }

    public function createEvent()
    {
        $eventData = $_POST;
        $apiService = new ApiService();
        $response = $apiService->fetch('/events', 'POST', $eventData);
        if (!$response) {
            $errorMsg = 'Unable to connect to event service. Please try again later.';
            echo $this->twig->render('app/eventsList.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }
        if (isset($response['error'])) {
            $errorMsg = $response['error'];
            echo $this->twig->render('app/eventsList.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }
        if (isset($response['success']) && $response['success']) {
            echo $this->twig->render('app/eventsList.twig', [
                'success' => 'Event created successfully',
                'profile' => $response['data']
            ]);
            return;
        } else {
            $errorMsg = 'Event creation failed. Please check your input and try again.';
            echo $this->twig->render('app/eventsList.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }

    }
}
