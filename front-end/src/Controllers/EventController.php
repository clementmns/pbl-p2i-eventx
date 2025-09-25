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

    public function eventView()
    {
        if (!$this->sessionManager->get('user')) {
            header('Location: /login');
            exit;
        }

        $apiService = new ApiService();
        $eventsResponse = $apiService->fetch('/events', 'GET');
        $events = $eventsResponse['data'] ?? [];
        echo $this->twig->render('app/eventsList.twig', [
            'profile' => $this->sessionManager->get('profile') ?? [],
            'events' => $events
        ]);
    }

    public function createEventView()
    {
        if (!$this->sessionManager->get('user')) {
            header('Location: /login');
            exit;
        }

        echo $this->twig->render('app/createEvent.twig', [
            'profile' => $this->sessionManager->get('profile') ?? []
        ]);
    }

    public function createEvent()
    {
        if (!$this->sessionManager->get('user')) {
            header('Location: /login');
            exit;
        }

        $eventData = $_POST;
        $apiService = new ApiService();
        $response = $apiService->fetch('/events', 'POST', $eventData);
        if ($response && isset($response['success']) && $response['success']) {
            echo $this->twig->render('app/eventsList.twig', [
                'success' => 'Event created successfully',
                'profile' => $response['data']
            ]);
            return;
        } else {
            echo $this->twig->render('app/eventsList.twig', [
                'errors' => ['Event creation failed.'],
                'profile' => null
            ]);
            return;
        }

    }
}
