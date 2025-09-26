#include "LoginView.h"

LoginView::LoginView(sf::RenderWindow& window, sf::Font& font)
    : View(window, font),
      emailLabel(font),
      emailText(font),
      passwordLabel(font),
      passwordText(font),
      loginButtonText(font),
      statusText(font) {
    setupUI();
}

void LoginView::draw() {
    // Apply fade-out effect during transition
    uint8_t opacity = static_cast<uint8_t>(255 * (1.0f - transitionAlpha));

    // Set colors with appropriate opacity
    sf::Color textColor = sf::Color(0, 0, 0, opacity);
    emailLabel.setFillColor(textColor);
    emailText.setFillColor(textColor);
    passwordLabel.setFillColor(textColor);
    passwordText.setFillColor(textColor);
    loginButtonText.setFillColor(sf::Color(255, 255, 255, opacity));

    sf::Color boxColor = sf::Color(240, 240, 240, opacity);
    emailBox.setFillColor(boxColor);
    passwordBox.setFillColor(boxColor);
    loginButton.setFillColor(sf::Color(0, 0, 0, opacity));

    // Draw all UI elements
    window.draw(emailLabel);
    window.draw(emailBox);
    window.draw(emailText);
    window.draw(passwordLabel);
    window.draw(passwordBox);
    window.draw(passwordText);
    window.draw(loginButton);
    window.draw(loginButtonText);
    window.draw(statusText);
}

void LoginView::handleEvent(const sf::Event& event) {
    if (isTransitioning) {
        return; // Ignore events during transition
    }

    if (const auto* mouseEvent = event.getIf<sf::Event::MouseButtonPressed>()) {
        sf::Vector2f mousePos = window.mapPixelToCoords(sf::Mouse::getPosition(window));

        // Check if email field clicked
        if (emailBox.getGlobalBounds().contains(mousePos)) {
            isEmailFieldActive = true;
            isPasswordFieldActive = false;
            emailBox.setOutlineThickness(2);
            emailBox.setOutlineColor(sf::Color::Blue);
            passwordBox.setOutlineThickness(0);
        }
        // Check if password field clicked
        else if (passwordBox.getGlobalBounds().contains(mousePos)) {
            isPasswordFieldActive = true;
            isEmailFieldActive = false;
            passwordBox.setOutlineThickness(2);
            passwordBox.setOutlineColor(sf::Color::Blue);
            emailBox.setOutlineThickness(0);
        }
        // Check if login button clicked
        else if (loginButton.getGlobalBounds().contains(mousePos)) {
            if (emailInput.empty() || passwordInput.empty()) {
                setStatusMessage("Please fill in all fields", sf::Color::Red);
            } else if (onLoginClicked) {
                onLoginClicked(emailInput, passwordInput);
            }
        }
        else {
            isEmailFieldActive = false;
            isPasswordFieldActive = false;
            emailBox.setOutlineThickness(0);
            passwordBox.setOutlineThickness(0);
        }
    }
    else if (const auto* textEvent = event.getIf<sf::Event::TextEntered>()) {
        // Handle text input for active field
        if (isEmailFieldActive) {
            if (textEvent->unicode == 8 && !emailInput.empty()) { // Backspace
                emailInput.pop_back();
            }
            else if (textEvent->unicode >= 32 && textEvent->unicode < 128) { // Printable ASCII
                emailInput += static_cast<char>(textEvent->unicode);
            }
            emailText.setString(emailInput);
        }
        else if (isPasswordFieldActive) {
            if (textEvent->unicode == 8 && !passwordInput.empty()) { // Backspace
                passwordInput.pop_back();
            }
            else if (textEvent->unicode >= 32 && textEvent->unicode < 128) { // Printable ASCII
                passwordInput += static_cast<char>(textEvent->unicode);
            }
            // Show asterisks for password
            std::string passwordDisplay(passwordInput.length(), '*');
            passwordText.setString(passwordDisplay);
        }
    }
}

void LoginView::update(float deltaTime) {
    // Update transition if needed
    if (isTransitioning) {
        float elapsed = transitionClock.getElapsedTime().asSeconds();
        transitionAlpha = elapsed / transitionDuration;

        if (transitionAlpha >= 1.0f) {
            transitionAlpha = 1.0f;
            isTransitioning = false;
        }
    }
}

void LoginView::setStatusMessage(const std::string& message, const sf::Color& color) {
    statusText.setString(message);
    statusText.setFillColor(color);
}

void LoginView::setLoggingIn(bool isLoggingIn) {
    if (isLoggingIn) {
        setStatusMessage("Logging in...", sf::Color::Blue);
    }
}

void LoginView::startTransitionOut() {
    isTransitioning = true;
    transitionClock.restart();
}

bool LoginView::isTransitioningOut() const {
    return isTransitioning;
}

float LoginView::getTransitionAlpha() const {
    return transitionAlpha;
}

std::string LoginView::getEmail() const {
    return emailInput;
}

std::string LoginView::getPassword() const {
    return passwordInput;
}

void LoginView::setOnLoginClicked(std::function<void(const std::string&, const std::string&)> callback) {
    onLoginClicked = callback;
}

void LoginView::setupUI() {
    // Email label and input box
    emailLabel.setFont(font);
    emailLabel.setString("Email:");
    emailLabel.setCharacterSize(20);
    emailLabel.setFillColor(sf::Color::Black);

    emailBox.setSize(sf::Vector2f(400, 30));
    emailBox.setFillColor(sf::Color(240, 240, 240));

    emailText.setFont(font);
    emailText.setString("");
    emailText.setCharacterSize(20);
    emailText.setFillColor(sf::Color::Black);

    // Password label and input box
    passwordLabel.setFont(font);
    passwordLabel.setString("Password:");
    passwordLabel.setCharacterSize(20);
    passwordLabel.setFillColor(sf::Color::Black);

    passwordBox.setSize(sf::Vector2f(400, 30));
    passwordBox.setFillColor(sf::Color(240, 240, 240));

    passwordText.setFont(font);
    passwordText.setString("");
    passwordText.setCharacterSize(20);
    passwordText.setFillColor(sf::Color::Black);

    // Login button
    loginButton.setSize(sf::Vector2f(400, 40));
    loginButton.setFillColor(sf::Color::Black);

    loginButtonText.setFont(font);
    loginButtonText.setString("Login");
    loginButtonText.setCharacterSize(20);
    loginButtonText.setFillColor(sf::Color::White);

    // Status text for feedback
    statusText.setFont(font);
    statusText.setString("");
    statusText.setCharacterSize(18);
    statusText.setFillColor(sf::Color::Red);

    // Positioning for login form - centered layout
    const float centerX = 200;
    emailLabel.setPosition({centerX, 150});
    emailBox.setPosition({centerX, 180});
    emailText.setPosition({centerX + 10, 185});

    passwordLabel.setPosition({centerX, 230});
    passwordBox.setPosition({centerX, 260});
    passwordText.setPosition({centerX + 10, 265});

    loginButton.setPosition({centerX, 320});

    // Center the text on the button
    sf::FloatRect textBounds = loginButtonText.getLocalBounds();
    loginButtonText.setPosition({
        centerX + (400 - textBounds.size.x) / 2,
        320 + (40 - textBounds.size.y) / 2 - 5
    });

    statusText.setPosition({centerX, 380});
}
