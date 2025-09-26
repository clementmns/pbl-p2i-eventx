#pragma once
#include "View.h"
#include "../models/User.h"
#include <vector>
#include <functional>

// A class to represent a table cell
class TableCell {
public:
    TableCell(sf::Font& font, const std::string& text, float width, float height, bool isHeader = false);

    void setPosition(float x, float y);
    void draw(sf::RenderWindow& window);

    float getWidth() const;
    float getHeight() const;
    sf::Text& getText() { return textObj; }
    const sf::Text& getText() const { return textObj; }
    sf::Vector2f getPosition() const { return rect.getPosition(); }

    // Making rect accessible for event handling
    sf::RectangleShape rect;
private:
    sf::Text textObj;
    float width;
    float height;
};

class HomeView : public View {
public:
    HomeView(sf::RenderWindow& window, sf::Font& font);

    void draw() override;
    void handleEvent(const sf::Event& event) override;
    void update(float deltaTime) override;

    // Set data for the view
    void setUsers(const std::vector<User>& users);
    void setError(const std::string& message);
    void setWelcomeMessage(const std::string& message);
    void setUserInfo(const std::string& info);

    // Start transition in
    void startTransitionIn();

    // Set callback for delete user action
    void setOnDeleteUser(std::function<void(int)> callback);

private:
    // UI elements
    sf::Text welcomeText;
    sf::Text userInfoText;

    // Users table
    std::vector<TableCell> headers;
    std::vector<std::vector<TableCell>> rows;

    // Table state
    bool isLoading = true;
    bool hasError = false;
    std::string errorMessage;

    sf::Text loadingText;
    sf::Text errorText;

    // Table dimensions
    const float tableX = 50.0f;
    const float tableY = 100.0f;
    const float headerHeight = 30.0f;
    const float rowHeight = 25.0f;

    // Scrolling
    float scrollOffset = 0.0f;
    const float scrollSpeed = 30.0f;

    // Transition state
    bool isTransitioning = false;
    float transitionAlpha = 0.0f;
    sf::Clock transitionClock;
    const float transitionDuration = 0.5f;

    // Callback function
    std::function<void(int)> onDeleteUser;

    // Helper methods
    void setupUI();
    void drawUserTable();
};
