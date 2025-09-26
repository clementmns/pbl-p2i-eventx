#pragma once
#include "../models/AppPage.h"
#include "../models/ApiService.h"
#include "../models/AuthData.h"
#include "../views/LoginView.h"
#include "../views/HomeView.h"
#include "../views/AccessDeniedView.h"
#include <SFML/Graphics.hpp>
#include <optional>
#include <memory>

class AppController {
public:
    AppController(sf::RenderWindow& window, sf::Font& font);

    // Main application methods
    void handleEvent(const sf::Event& event);
    void update(float deltaTime);
    void render();

private:
    // Window reference
    sf::RenderWindow& window;
    sf::Font& font;

    // Current app state
    AppPage currentPage = AppPage::Login;

    // Models
    ApiService apiService;
    std::optional<AuthData> authData;

    // Views
    std::unique_ptr<LoginView> loginView;
    std::unique_ptr<HomeView> homeView;
    std::unique_ptr<AccessDeniedView> accessDeniedView;

    // Controller methods
    void setupViews();
    void handleLogin(const std::string& email, const std::string& password);
    void navigateToHome();
    void navigateToAccessDenied();
    void navigateToLogin();
    void fetchUsers();
    void handleDeleteUser(int userId);
};
