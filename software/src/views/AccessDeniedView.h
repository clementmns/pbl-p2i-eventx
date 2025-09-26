#pragma once
#include "View.h"

class AccessDeniedView : public View {
public:
    AccessDeniedView(sf::RenderWindow& window, sf::Font& font);

    void draw() override;
    void handleEvent(const sf::Event& event) override;
    void update(float deltaTime) override;

    // Set callback for back button
    void setOnBackClicked(std::function<void()> callback);

private:
    // UI elements
    sf::Text titleText;
    sf::Text messageText;
    sf::RectangleShape backButton;
    sf::Text backButtonText;

    // Callback function
    std::function<void()> onBackClicked;

    // Helper methods
    void setupUI();
};
