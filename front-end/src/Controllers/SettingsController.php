<?php
namespace Controllers;

use Services\ApiService;
use Services\SessionManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SettingsController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Templates');
        $this->twig = new Environment($loader);
    }

    public function settingsView()
    {
        echo $this->twig->render('app/settings.twig', [
            'profile' => $_SESSION['user']['profile'] ?? []
        ]);
    }

    public function settings()
    {
        $profileData = $_POST;
        $sessionManager = new SessionManager();
        $user = $sessionManager->get('user');
        if (!$user || !isset($user['id'])) {
            echo $this->twig->render('app/settings.twig', [
                'errors' => ['User not authenticated'],
                'profile' => null
            ]);
            return;
        }
        $userId = $user['id'];
        $apiService = new ApiService();
        $endpoint = "/profiles/user/{$userId}";
        $response = $apiService->fetch($endpoint, 'PUT', $profileData);
        if (isset($response)) {
            $updatedUser = $apiService->fetch("/users/{$userId}", 'GET');
            if ($updatedUser) {
                $sessionManager->set('user', $updatedUser);
            }
            echo $this->twig->render('app/settings.twig', [
                'success' => 'Profile updated successfully',
                'profile' => $profileData
            ]);
            return;
        } else {
            $errorMsg = 'Profile update failed. Please check your input and try again.';
            echo $this->twig->render('app/settings.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }
    }
}
