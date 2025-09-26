#include <SFML/Graphics.hpp>
#include <iostream>
#include "controllers/AppController.h"

int main()
{
  sf::RenderWindow window(sf::VideoMode({800, 600}), "EventX Internal App");
  window.setFramerateLimit(60);

  sf::Font font;
  if (!font.openFromFile("../src/assets/fonts/Roboto/static/Roboto-Regular.ttf"))
  {
    std::cerr << "Failed to load font\n";
    return -1;
  }

  // Create main application controller
  AppController app(window, font);

  // Main application loop
  while (window.isOpen())
  {
    // Process events
    std::optional<sf::Event> eventOpt = window.pollEvent();
    while (eventOpt.has_value())
    {
      const sf::Event& event = eventOpt.value();

      // Handle window close event
      if (event.is<sf::Event::Closed>())
      {
        window.close();
      }
      else
      {
        // Pass other events to the app controller
        app.handleEvent(event);
      }

      // Get next event
      eventOpt = window.pollEvent();
    }

    // Update application state
    app.update(1.0f / 60.0f); // Assuming 60 FPS

    // Render the application
    window.clear(sf::Color::White);
    app.render();
    window.display();
  }

  return 0;
}
