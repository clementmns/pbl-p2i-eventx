#include "AccessDeniedView.h"

AccessDeniedView::AccessDeniedView(sf::RenderWindow& window, sf::Font& font)
    : View(window, font), titleText(font), messageText(font), backButtonText(font) {
    setupUI();
}

void AccessDeniedView::draw() {
    window.draw(titleText);
    window.draw(messageText);
    window.draw(backButton);
    window.draw(backButtonText);
}

void AccessDeniedView::handleEvent(const sf::Event& event) {
    if (event.is<sf::Event::MouseButtonPressed>()) {
        sf::Vector2f mousePos = window.mapPixelToCoords(sf::Mouse::getPosition(window));

        // Check if back button clicked
        if (backButton.getGlobalBounds().contains(mousePos) && onBackClicked) {
            onBackClicked();
        }
    }
}void AccessDeniedView::update(float deltaTime) {
    // Nothing to update
}

void AccessDeniedView::setOnBackClicked(std::function<void()> callback) {
    onBackClicked = callback;
}

void AccessDeniedView::setupUI() {
    // Setup title text
    titleText.setFont(font);
    titleText.setString("Access Denied");
    titleText.setCharacterSize(36);
    titleText.setFillColor(sf::Color::Red);
    titleText.setStyle(sf::Text::Bold);

    // Center the title text
    sf::FloatRect titleBounds = titleText.getLocalBounds();
    titleText.setPosition({
        (window.getSize().x - titleBounds.size.x) / 2,
        150
    });

    // Setup message text
    messageText.setFont(font);
    messageText.setString("You don't have permission to access this area.\nOnly admin users can view this page.");
    messageText.setCharacterSize(20);
    messageText.setFillColor(sf::Color::Black);

    // Center the message text
    sf::FloatRect messageBounds = messageText.getLocalBounds();
    messageText.setPosition({
        (window.getSize().x - messageBounds.size.x) / 2,
        230
    });

    // Back button
    backButton.setSize(sf::Vector2f(200, 40));
    backButton.setFillColor(sf::Color::Black);

    backButtonText.setFont(font);
    backButtonText.setString("Back to Login");
    backButtonText.setCharacterSize(18);
    backButtonText.setFillColor(sf::Color::White);

    // Position the button
    backButton.setPosition({300, 320});

    // Center the text on the button
    sf::FloatRect backTextBounds = backButtonText.getLocalBounds();
    backButtonText.setPosition({
        300 + (200 - backTextBounds.size.x) / 2,
        320 + (40 - backTextBounds.size.y) / 2 - 5
    });
}
