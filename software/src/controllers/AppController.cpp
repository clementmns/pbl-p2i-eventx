#include "AppController.h"
#include <iostream>

AppController::AppController(sf::RenderWindow& window, sf::Font& font)
    : window(window), font(font), apiService("localhost", 8000) {
    setupViews();
}

void AppController::handleEvent(const sf::Event& event) {
    switch (currentPage) {
        case AppPage::Login:
            loginView->handleEvent(event);
            break;
        case AppPage::Home:
            homeView->handleEvent(event);
            break;
        case AppPage::AccessDenied:
            accessDeniedView->handleEvent(event);
            break;
    }
}

void AppController::update(float deltaTime) {
    switch (currentPage) {
        case AppPage::Login:
            loginView->update(deltaTime);
            break;
        case AppPage::Home:
            homeView->update(deltaTime);
            break;
        case AppPage::AccessDenied:
            accessDeniedView->update(deltaTime);
            break;
    }
}

void AppController::render() {
    switch (currentPage) {
        case AppPage::Login:
            loginView->draw();
            break;
        case AppPage::Home:
            homeView->draw();
            break;
        case AppPage::AccessDenied:
            accessDeniedView->draw();
            break;
    }
}

void AppController::setupViews() {
    // Create views
    loginView = std::make_unique<LoginView>(window, font);
    homeView = std::make_unique<HomeView>(window, font);
    accessDeniedView = std::make_unique<AccessDeniedView>(window, font);

    // Set up login view callbacks
    loginView->setOnLoginClicked([this](const std::string& email, const std::string& password) {
        handleLogin(email, password);
    });

    // Set up home view callbacks
    homeView->setWelcomeMessage("Welcome to EventX Admin Dashboard");
    homeView->setOnDeleteUser([this](int userId) {
        handleDeleteUser(userId);
    });

    // Set up access denied view callbacks
    accessDeniedView->setOnBackClicked([this]() {
        navigateToLogin();
    });
}

void AppController::handleLogin(const std::string& email, const std::string& password) {
    // Show "logging in" message
    loginView->setLoggingIn(true);

    // Render the login view with the "logging in" message before making the API request
    window.clear(sf::Color::White);
    loginView->draw();
    window.display();

    // Send login request
    std::string errorMessage;
    authData = apiService.login(email, password, errorMessage);

    if (authData) {
        // Login successful
        loginView->setStatusMessage("Login successful!", sf::Color::Green);
        // Directly navigate to Home instead of waiting for transition
        if (authData->isAdmin()) {
            navigateToHome();
        } else {
            navigateToAccessDenied();
        }
    } else {
        // Login failed
        loginView->setStatusMessage(errorMessage, sf::Color::Red);
    }
}

void AppController::navigateToHome() {
    // Simply change the current page to Home
    currentPage = AppPage::Home;

    // Set user info if available
    if (authData) {
        std::string userInfo = "Logged in as: " + authData->getUsername() + " (Admin)";
        homeView->setUserInfo(userInfo);
    } else {
        homeView->setError("Not authenticated");
    }

    // Fetch users for the table
    fetchUsers();
}

void AppController::navigateToAccessDenied() {
    currentPage = AppPage::AccessDenied;
}

void AppController::navigateToLogin() {
    currentPage = AppPage::Login;
    authData = std::nullopt;
}

void AppController::fetchUsers() {
    if (!authData) {
        homeView->setError("Not authenticated");
        return;
    }

    std::string errorMessage;
    auto users = apiService.fetchUsers(authData->getToken(), errorMessage);

    if (users) {
        homeView->setUsers(*users);
    } else {
        homeView->setError(errorMessage);
    }
}

void AppController::handleDeleteUser(int userId) {
    if (!authData) {
        return;
    }

    if (apiService.deleteUser(userId, authData->getToken())) {
        // Refresh the user list after deletion
        fetchUsers();
    } else {
        homeView->setError("Failed to delete user");
    }
}
