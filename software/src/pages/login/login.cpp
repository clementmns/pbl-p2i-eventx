#include <SFML/Graphics.hpp>
#include <string>
#include <iostream>
#include "ui_setup.cpp"

void runLogin(sf::RenderWindow& window, sf::Font& font)
{
    sf::Text emailLabel(font), emailText(font), passwordLabel(font), passwordText(font), loginButtonText(font);
    std::string emailInput, passwordInput;
    sf::RectangleShape emailBox, passwordBox, loginButton;
    setupUI(font, emailLabel, emailBox, emailText, passwordLabel, passwordBox, passwordText, loginButton, loginButtonText);

    bool isPasswordField = false;

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
}
