<?php
namespace Controllers;

use Services\ApiService;
use Services\SessionManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SettingsController
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

    public function settingsView()
    {
        $flash = $this->sessionManager->getFlash();
        echo $this->twig->render('app/settings.twig', [
            'profile' => $_SESSION['user']['profile'] ?? [],
            'user' => $_SESSION['user'] ?? [],
            'flash' => $flash
        ]);
    }

    public function settings()
    {
        $profileData = $_POST;
        $user = $this->sessionManager->get('user');
        if (!$user || !isset($user['id'])) {
            $this->sessionManager->setFlash('error', 'User not authenticated');
            header('Location: /settings');
            return;
        }
        $userId = $user['id'];
        $apiService = new ApiService();
        $endpoint = "/profiles/user/{$userId}";
        $response = $apiService->fetch($endpoint, 'PUT', $profileData);
        if (isset($response)) {
            $updatedUser = $apiService->fetch("/users/{$userId}", 'GET');
            if ($updatedUser) {
                $this->sessionManager->set('user', $updatedUser);
            }
            $this->sessionManager->setFlash('success', 'Profile updated successfully');
            header('Location: /settings');
            return;
        } else {
            $errorMsg = 'Profile update failed. Please check your input and try again.';
            echo $this->twig->render('app/settings.twig', [
                'user' => $_SESSION['user'] ?? [],
                'errors' => [$errorMsg],
            ]);
            return;
        }
    }
}
