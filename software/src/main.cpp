#include <SFML/Graphics.hpp>
#include <iostream>
#include "./pages/login/login.cpp"

int main()
{
  sf::RenderWindow window(sf::VideoMode({800, 600}), "EventX Internal App");

  sf::Font font;
  if (!font.openFromFile("../src/assets/fonts/Roboto/static/Roboto-Regular.ttf"))
  {
    std::cerr << "Failed to load font\n";
    return -1;
  }

  sf::Text emailLabel(font), emailText(font), passwordLabel(font), passwordText(font), loginButtonText(font);
  std::string emailInput, passwordInput;
  sf::RectangleShape emailBox, passwordBox, loginButton;
  setupUI(font, emailLabel, emailBox, emailText, passwordLabel, passwordBox, passwordText, loginButton, loginButtonText);

  bool isPasswordField = false;

  runLogin(window, font);

  while (window.isOpen())
  {
    window.clear(sf::Color::White);
    window.draw(emailLabel);
    window.draw(emailBox);
    window.draw(emailText);
    window.draw(passwordLabel);
    window.draw(passwordBox);
    window.draw(passwordText);
    window.draw(loginButton);
    window.draw(loginButtonText);
    window.display();
  }

  return 0;
}
