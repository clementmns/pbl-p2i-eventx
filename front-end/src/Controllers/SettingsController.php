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
            'profile' => $_SESSION['profile'] ?? []
        ]);
    }

    public function settings()
    {
        $profileData = $_POST;
        $apiService = new ApiService();
        $response = $apiService->fetch('/settings', 'POST', $profileData);
        if ($response && isset($response['success']) && $response['success']) {
            echo $this->twig->render('app/settings.twig', [
                'success' => 'Settings updated successfully',
                'profile' => $response['data']
            ]);
            return;
        } else {
            echo $this->twig->render('app/settings.twig', [
                'errors' => ['Settings update failed.'],
                'profile' => null
            ]);
            return;
        }

    }
}
