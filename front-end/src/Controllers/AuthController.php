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
        echo $this->twig->render('auth/login.twig');
    }

    public function login($mail, $password)
    {
        $apiService = new ApiService();
        if (!empty($mail) && !empty($password)) {
            $response = $apiService->fetch('/auth/login', 'POST', ['mail' => $mail, 'password' => $password]);
            if (!$response || !isset($response['user'])) {
                echo $this->twig->render('auth/login.twig', ['error' => 'Invalid credentials']);
                return;
            }

            $session = new SessionManager();
            $session->start();
            $session->set('user', $response['user']);
            header('Location: /');
            exit;
        }

        echo $this->twig->render('auth/login.twig', ['error' => 'Invalid fields']);
    }

    public function registerView()
    {
        echo $this->twig->render('auth/register.twig');
    }

    public function register($mail, $password, $confirm_password)
    {
        $apiService = new ApiService();
        if (!empty($mail) && !empty($password) && ($password === $confirm_password)) {
            $response = $apiService->fetch('/auth/register', 'POST', ['mail' => $mail, 'password' => $password]);
            if ($response && isset($response['user'])) {
                $this->login($mail, $password);
                header('Location: /login');
                exit;
            } else {
                echo $this->twig->render('auth/register.twig', ['error' => 'Registration failed']);
                return;
            }
        }
        echo $this->twig->render('auth/register.twig', ['error' => 'Invalid fields']);
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
