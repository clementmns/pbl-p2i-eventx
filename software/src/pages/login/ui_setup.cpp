#include <SFML/Graphics.hpp>
#include <string>

void setupUI(
    sf::Font &font,
    sf::Text &emailLabel,
    sf::RectangleShape &emailBox,
    sf::Text &emailText,
    sf::Text &passwordLabel,
    sf::RectangleShape &passwordBox,
    sf::Text &passwordText,
    sf::RectangleShape &loginButton,
    sf::Text &loginButtonText)
{
  // email label and input box
  emailLabel.setFont(font);
  emailLabel.setString("Email:");
  emailLabel.setCharacterSize(20);
  emailLabel.setFillColor(sf::Color::Black);

  emailBox.setSize(sf::Vector2f(400, 30));
  emailBox.setFillColor(sf::Color(200, 200, 200));

  emailText.setFont(font);
  emailText.setString("");
  emailText.setCharacterSize(20);
  emailText.setFillColor(sf::Color::Black);

  // password label and input box
  passwordLabel.setFont(font);
  passwordLabel.setString("Password:");
  passwordLabel.setCharacterSize(20);
  passwordLabel.setFillColor(sf::Color::Black);

  passwordBox.setSize(sf::Vector2f(400, 30));
  passwordBox.setFillColor(sf::Color(200, 200, 200));

  passwordText.setFont(font);
  passwordText.setString("");
  passwordText.setCharacterSize(20);
  passwordText.setFillColor(sf::Color::Black);

  // login button
  loginButton.setSize(sf::Vector2f(100, 40));
  loginButton.setFillColor(sf::Color(100, 100, 250));

  loginButtonText.setFont(font);
  loginButtonText.setString("Login");
  loginButtonText.setCharacterSize(20);
  loginButtonText.setFillColor(sf::Color::White);

  // positioning for login form
  emailLabel.setPosition({200, 150});
  emailBox.setPosition({200, 180});
  emailText.setPosition({210, 185});

  passwordLabel.setPosition({200, 230});
  passwordBox.setPosition({200, 260});
  passwordText.setPosition({210, 265});

  loginButton.setPosition({350, 320});
  loginButtonText.setPosition({370, 325});
}
