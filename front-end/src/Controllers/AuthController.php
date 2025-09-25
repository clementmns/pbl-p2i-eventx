<?php
namespace Controllers;

use Services\ApiService;
use Services\SessionManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AuthController
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

    public function loginView()
    {
        $flash = $this->sessionManager->getFlash();
        echo $this->twig->render('auth/login.twig', ['flash' => $flash]);
    }

    public function login($mail, $password)
    {
        $apiService = new ApiService();

        if (empty($mail) || empty($password)) {
            $this->sessionManager->setFlash('error', 'Please fill in both email and password fields.');
            header('Location: /login');
            return;
        }

        $response = $apiService->fetch('/auth/login', 'POST', ['mail' => $mail, 'password' => $password]);

        if (!isset($response['user'])) {
            $this->sessionManager->setFlash('error', 'Incorrect email or password. Please check your credentials.');
            header('Location: /login');
            return;
        }

        $session = new SessionManager();
        $session->start();
        $session->set('user', $response['user']);
        header('Location: /');
        exit;
    }

    public function registerView()
    {
        $flash = $this->sessionManager->getFlash();
        echo $this->twig->render('auth/register.twig', ['flash' => $flash]);
    }

    public function register($mail, $password, $confirm_password)
    {
        $apiService = new ApiService();

        if (empty($mail) || empty($password)) {
            $this->sessionManager->setFlash('error', 'Please fill in all required fields.');
            header('Location: /register');
            return;
        }

        if ($password !== $confirm_password) {
            $this->sessionManager->setFlash('error', 'Passwords do not match.');
            header('Location: /register');
            return;
        }

        $response = $apiService->fetch('/auth/register', 'POST', ['mail' => $mail, 'password' => $password]);

        if (!isset($response['user'])) {
            $this->sessionManager->setFlash('error', 'Registration failed. Please check your details and try again.');
            header('Location: /register');
            return;
        }

        $this->sessionManager->setFlash('success', 'Registration successful! Please login.');
        header('Location: /login');
    }

    public function logout()
    {
        $session = new SessionManager();
        $session->start();
        $session->destroy();
        header('Location: /login');
        exit;
    }
}
