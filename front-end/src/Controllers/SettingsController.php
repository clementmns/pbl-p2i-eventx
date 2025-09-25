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
        if (!$response) {
            $errorMsg = 'Unable to connect to settings service. Please try again later.';
            echo $this->twig->render('app/settings.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }
        if (isset($response['error'])) {
            $errorMsg = $response['error'] ?? 'An unknown error occurred.';
            echo $this->twig->render('app/settings.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }
        if (isset($response['success']) && $response['success']) {
            echo $this->twig->render('app/settings.twig', [
                'success' => 'Settings updated successfully',
                'profile' => $response['data']
            ]);
            return;
        } else {
            $errorMsg = 'Settings update failed. Please check your input and try again.';
            echo $this->twig->render('app/settings.twig', [
                'errors' => [$errorMsg],
                'profile' => null
            ]);
            return;
        }

    }
}
