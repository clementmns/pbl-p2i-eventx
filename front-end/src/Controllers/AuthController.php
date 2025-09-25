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
        if (!empty($mail) && !empty($password)) {
            $response = $apiService->fetch('/auth/login', 'POST', ['mail' => $mail, 'password' => $password]);
            if (!$response) {
                $this->sessionManager->setFlash('error', 'Unable to connect to authentication service. Please try again later.');
                header('Location: /login');
                return;
            }
            if (isset($response['error'])) {
                $this->sessionManager->setFlash('error', $response['error'] ?? 'An unknown error occurred.');
                header('Location: /login');
                return;
            }
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

        echo $this->twig->render('auth/login.twig', ['error' => 'Please fill in both email and password fields.']);
    }

    public function registerView()
    {
        echo $this->twig->render('auth/register.twig');
    }

    public function register($mail, $password, $confirm_password)
    {
        $apiService = new ApiService();
        if (!empty($mail) && !empty($password)) {
            if ($password !== $confirm_password) {
                $errorMsg = 'Passwords do not match. ' . $password . ' !== ' . $confirm_password;
                echo $this->twig->render('auth/register.twig', ['error' => $errorMsg]);
                return;
            }
            $response = $apiService->fetch('/auth/register', 'POST', ['mail' => $mail, 'password' => $password]);
            if (!$response) {
                $errorMsg = 'Unable to connect to registration service. Please try again later.';
                echo $this->twig->render('auth/register.twig', ['error' => $errorMsg]);
                return;
            }
            if (isset($response['error'])) {
                $errorMsg = $response['error'];
                echo $this->twig->render('auth/register.twig', ['error' => $errorMsg]);
                return;
            }
            if (isset($response['user'])) {
                $this->login($mail, $password);
                header('Location: /login');
                exit;
            } else {
                $errorMsg = 'Registration failed. Please check your details and try again.';
                echo $this->twig->render('auth/register.twig', ['error' => $errorMsg]);
                return;
            }
        }
        if ($password !== $confirm_password) {
            $errorMsg = 'Passwords do not match.';
        } else {
            $errorMsg = 'Please fill in all required fields.';
        }
        echo $this->twig->render('auth/register.twig', ['error' => $errorMsg]);
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
