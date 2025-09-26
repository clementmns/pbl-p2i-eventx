#pragma once
#include <SFML/Graphics.hpp>
#include <functional>
#include <string>

// Base View class that all views will inherit from
class View {
public:
    View(sf::RenderWindow& window, sf::Font& font);
    virtual ~View() = default;

    virtual void draw() = 0;
    virtual void handleEvent(const sf::Event& event) = 0;
    virtual void update(float deltaTime) = 0;

protected:
    sf::RenderWindow& window;
    sf::Font& font;
};
