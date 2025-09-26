#pragma once
#include "View.h"
#include <functional>

class LoginView : public View {
public:
    LoginView(sf::RenderWindow& window, sf::Font& font);

    void draw() override;
    void handleEvent(const sf::Event& event) override;
    void update(float deltaTime) override;

    // Setters for status messages
    void setStatusMessage(const std::string& message, const sf::Color& color);
    void setLoggingIn(bool isLoggingIn);

    // Transition effect
    void startTransitionOut();
    bool isTransitioningOut() const;
    float getTransitionAlpha() const;

    // Get input values
    std::string getEmail() const;
    std::string getPassword() const;

    // Set callback for login button click
    void setOnLoginClicked(std::function<void(const std::string&, const std::string&)> callback);

private:
    // UI elements
    sf::Text emailLabel, emailText, passwordLabel, passwordText, loginButtonText, statusText;
    sf::RectangleShape emailBox, passwordBox, loginButton;

    // Input state
    std::string emailInput, passwordInput;
    bool isEmailFieldActive = false;
    bool isPasswordFieldActive = false;

    // Transition state
    bool isTransitioning = false;
    float transitionAlpha = 0.0f;
    sf::Clock transitionClock;
    const float transitionDuration = 0.5f;

    // Callback functions
    std::function<void(const std::string&, const std::string&)> onLoginClicked;

    // Helper methods for UI setup
    void setupUI();
};
