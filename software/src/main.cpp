#include <SFML/Graphics.hpp>
#include <SFML/Network.hpp>
#include <iostream>
#include <string>
#include <optional>
#include <sstream>
#include <vector>
#include <map>
#include <functional> // For std::function

// Application pages enum
enum class AppPage {
  Login,
  Home,
  AccessDenied
};

// Structure to store authentication data
struct AuthData {
  std::string token;
  int roleId;
  std::string username;
};

// Structure to represent a user
struct User {
  int id;
  std::string email;
  int roleId;
};

// Simple JSON parsing function to extract a string value for a given key
std::string extractJsonValue(const std::string& json, const std::string& key) {
  std::string searchStr = "\"" + key + "\":";
  size_t pos = json.find(searchStr);
  if (pos == std::string::npos) return "";

  pos += searchStr.length();
  // Skip whitespace
  while (pos < json.length() && std::isspace(json[pos])) pos++;

  if (pos >= json.length()) return "";

  if (json[pos] == '"') {
    // String value
    pos++; // Skip opening quote
    std::string result;
    while (pos < json.length() && json[pos] != '"') {
      if (json[pos] == '\\' && pos + 1 < json.length()) {
        // Handle escaped characters
        pos++;
      }
      result += json[pos];
      pos++;
    }
    return result;
  } else if (std::isdigit(json[pos]) || json[pos] == '-') {
    // Number value
    std::string result;
    while (pos < json.length() && (std::isdigit(json[pos]) || json[pos] == '.' || json[pos] == '-')) {
      result += json[pos];
      pos++;
    }
    return result;
  } else if (json.substr(pos, 4) == "true") {
    return "true";
  } else if (json.substr(pos, 5) == "false") {
    return "false";
  } else if (json.substr(pos, 4) == "null") {
    return "null";
  }

  return "";
}

// Parse login response to extract authentication data
std::optional<AuthData> parseLoginResponse(const std::string& response) {
  try {
    AuthData auth;
    auth.token = extractJsonValue(response, "token");
    std::string roleIdStr = extractJsonValue(response, "roleId");

    if (!roleIdStr.empty() && std::all_of(roleIdStr.begin(), roleIdStr.end(), ::isdigit)) {
      auth.roleId = std::stoi(roleIdStr);
    } else {
      auth.roleId = -1; // Default to -1 if roleId is missing or invalid
    }

    if (auth.token.empty()) {
      std::cerr << "Failed to parse token from response: " << response << std::endl;
      return std::nullopt;
    }

    return auth;
  } catch (const std::exception& e) {
    std::cerr << "Error parsing login response: " << e.what() << std::endl;
    return std::nullopt;
  }
}

// Parse users list from JSON response
std::vector<User> parseUsersResponse(const std::string& response) {
  std::vector<User> users;

  // Find the start of the array
  size_t startPos = response.find('[');
  size_t endPos = response.rfind(']');

  if (startPos == std::string::npos || endPos == std::string::npos || startPos >= endPos) {
    std::cerr << "Invalid JSON array format" << std::endl;
    return users;
  }

  std::string arrayContent = response.substr(startPos + 1, endPos - startPos - 1);

  // Extract each user object from the array
  size_t objStart = 0;
  size_t nestLevel = 0;
  std::string currentObj;

  for (size_t i = 0; i < arrayContent.length(); i++) {
    char c = arrayContent[i];

    if (c == '{') {
      if (nestLevel == 0) {
        objStart = i;
      }
      nestLevel++;
    } else if (c == '}') {
      nestLevel--;
      if (nestLevel == 0) {
        // Found complete object
        currentObj = arrayContent.substr(objStart, i - objStart + 1);

        // Parse user object
        User user;
        try {
          std::string idStr = extractJsonValue(currentObj, "id");
          user.id = std::stoi(idStr);
          user.email = extractJsonValue(currentObj, "mail");
          std::string roleIdStr = extractJsonValue(currentObj, "roleId");
          user.roleId = std::stoi(roleIdStr);

          users.push_back(user);
        } catch (const std::exception& e) {
          std::cerr << "Error parsing user object: " << e.what() << std::endl;
        }
      }
    }
  }

  return users;
}

// Fetch users from the API with token authentication
std::optional<std::vector<User>> fetchUsers(const std::string& token, std::string& errorMessage) {
  sf::Http http("localhost", 8000);
  sf::Http::Request request;
  request.setMethod(sf::Http::Request::Method::Get);
  request.setUri("/api/users");
  request.setHttpVersion(1, 1);
  request.setField("Authorization", "Bearer " + token);

  sf::Http::Response httpResponse = http.sendRequest(request);

  if (httpResponse.getStatus() == sf::Http::Response::Status::Ok) {
    std::string responseBody = httpResponse.getBody();
    std::vector<User> users = parseUsersResponse(responseBody);
    return users;
  } else {
    std::ostringstream errorMsg;
    errorMsg << "Error fetching users: ";

    switch(httpResponse.getStatus()) {
      case sf::Http::Response::Status::BadRequest:
        errorMsg << "Bad Request (400)";
        break;
      case sf::Http::Response::Status::Unauthorized:
        errorMsg << "Unauthorized (401)";
        break;
      case sf::Http::Response::Status::Forbidden:
        errorMsg << "Forbidden (403)";
        break;
      case sf::Http::Response::Status::NotFound:
        errorMsg << "Not Found (404)";
        break;
      case sf::Http::Response::Status::InternalServerError:
        errorMsg << "Internal Server Error (500)";
        break;
      case sf::Http::Response::Status::ConnectionFailed:
        errorMsg << "Connection Failed";
        break;
      default:
        errorMsg << "Error " << static_cast<int>(httpResponse.getStatus());
    }

    errorMsg << " - " << httpResponse.getBody();
    errorMessage = errorMsg.str();
    return std::nullopt;
  }
};

std::optional<AuthData> sendLoginRequest(const std::string& email, const std::string& password, std::string& errorMessage) {
  sf::Http http("localhost", 8000);
  sf::Http::Request request;
  request.setMethod(sf::Http::Request::Method::Post);
  request.setUri("/api/auth/login");
  request.setHttpVersion(1, 1);
  request.setField("Content-Type", "application/json");

  std::ostringstream payload;
  payload << "{\"mail\":\"" << email << "\",\"password\":\"" << password << "\"}";

  request.setBody(payload.str());

  sf::Http::Response httpResponse = http.sendRequest(request);

  if (httpResponse.getStatus() == sf::Http::Response::Status::Ok) {
    std::string responseBody = httpResponse.getBody();
    auto authData = parseLoginResponse(responseBody);

    if (!authData) {
      errorMessage = "Failed to parse login response";
      return std::nullopt;
    }

    return authData;
  } else {
    std::ostringstream errorMsg;
    errorMsg << "Error: ";

    switch(httpResponse.getStatus()) {
      case sf::Http::Response::Status::BadRequest:
        errorMsg << "Bad Request (400)";
        break;
      case sf::Http::Response::Status::Unauthorized:
        errorMsg << "Unauthorized (401)";
        break;
      case sf::Http::Response::Status::Forbidden:
        errorMsg << "Forbidden (403)";
        break;
      case sf::Http::Response::Status::NotFound:
        errorMsg << "Not Found (404)";
        break;
      case sf::Http::Response::Status::InternalServerError:
        errorMsg << "Internal Server Error (500)";
        break;
      case sf::Http::Response::Status::ConnectionFailed:
        errorMsg << "Connection Failed";
        break;
      default:
        errorMsg << "Error " << static_cast<int>(httpResponse.getStatus());
    }

    errorMsg << " - " << httpResponse.getBody();
    errorMessage = errorMsg.str();
    return std::nullopt;
  }
}

void setupLoginUI(
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
  emailBox.setFillColor(sf::Color(240, 240, 240));

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
  passwordBox.setFillColor(sf::Color(240, 240, 240));

  passwordText.setFont(font);
  passwordText.setString("");
  passwordText.setCharacterSize(20);
  passwordText.setFillColor(sf::Color::Black);

  // login button - now matching width of input boxes and black color
  loginButton.setSize(sf::Vector2f(400, 40));
  loginButton.setFillColor(sf::Color::Black);

  loginButtonText.setFont(font);
  loginButtonText.setString("Login");
  loginButtonText.setCharacterSize(20);
  loginButtonText.setFillColor(sf::Color::White);

  // positioning for login form - centered layout
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
}

// Class to represent a table cell
class TableCell {
public:
  TableCell(sf::Font& font, const std::string& text, float width, float height, bool isHeader = false)
      : width(width), height(height), textObj(font) {
    rect.setSize(sf::Vector2f(width, height));
    rect.setFillColor(isHeader ? sf::Color(50, 50, 50) : sf::Color(240, 240, 240));
    rect.setOutlineThickness(1);
    rect.setOutlineColor(sf::Color(200, 200, 200));

    textObj.setFont(font);
    textObj.setString(text);
    textObj.setCharacterSize(16);
    textObj.setFillColor(isHeader ? sf::Color::White : sf::Color::Black);

    // Center text in cell
    sf::FloatRect textBounds = textObj.getLocalBounds();
    textObj.setPosition({
      (width - textBounds.size.x) / 2,
      (height - textBounds.size.y) / 2 - 4
    });
  }

  void setPosition(float x, float y) {
    rect.setPosition({x, y});

    sf::Vector2f rectPos = rect.getPosition();
    sf::Vector2f textPos = textObj.getPosition();

    textObj.setPosition({
      rectPos.x + textPos.x,
      rectPos.y + textPos.y
    });
  }

  void draw(sf::RenderWindow& window) {
    window.draw(rect);
    window.draw(textObj);
  }

  float getWidth() const { return width; }
  float getHeight() const { return height; }

private:
  sf::RectangleShape rect;
  sf::Text textObj;
  float width;
  float height;
};

// Class to represent a table of users
class UsersTable {
public:
  UsersTable(sf::Font& font, float x, float y, float width, float height)
      : font(font), x(x), y(y), width(width), height(height), loadingText(font), errorText(font) {
    // Calculate column widths
    float idWidth = width * 0.1f;
    float usernameWidth = width * 0.35f;
    float emailWidth = width * 0.35f;
    float roleWidth = width * 0.2f;

    // Create headers
    headers.push_back(TableCell(font, "ID", idWidth, headerHeight, true));
    headers.push_back(TableCell(font, "Username", usernameWidth, headerHeight, true));
    headers.push_back(TableCell(font, "Email", emailWidth, headerHeight, true));
    headers.push_back(TableCell(font, "Role", roleWidth, headerHeight, true));

    // Set header positions
    float currentX = x;
    for (auto& cell : headers) {
      cell.setPosition(currentX, y);
      currentX += cell.getWidth();
    }

    // Set loading text
    loadingText.setFont(font);
    loadingText.setString("Loading users...");
    loadingText.setCharacterSize(18);
    loadingText.setFillColor(sf::Color::Black);

    sf::FloatRect loadingBounds = loadingText.getLocalBounds();
    loadingText.setPosition({
      x + (width - loadingBounds.size.x) / 2,
      y + headerHeight + 20
    });

    // Set error text
    errorText.setFont(font);
    errorText.setCharacterSize(16);
    errorText.setFillColor(sf::Color::Red);
  }

  void setUsers(const std::vector<User>& users) {
    rows.clear();

    if (users.empty()) {
      return;
    }

    // Calculate column widths (same as headers)
    float idWidth = width * 0.1f;
    float usernameWidth = width * 0.35f;
    float emailWidth = width * 0.35f;
    float roleWidth = width * 0.2f;

    float currentY = y + headerHeight;

    for (const auto& user : users) {
      float currentX = x;

      // Create cells for this row
      std::vector<TableCell> row;

      row.push_back(TableCell(font, std::to_string(user.id), idWidth, rowHeight));
      row.push_back(TableCell(font, user.email, emailWidth, rowHeight));

      // Convert roleId to a readable string
      std::string roleText = "User";
      if (user.roleId == 2) {
        roleText = "Admin";
      }
      row.push_back(TableCell(font, roleText, roleWidth, rowHeight));

      // Set positions for cells in this row
      currentX = x;
      for (auto& cell : row) {
        cell.setPosition(currentX, currentY);
        currentX += cell.getWidth();
      }

      rows.push_back(row);
      currentY += rowHeight;
    }

    isLoading = false;
    hasError = false;
  }

  void setError(const std::string& message) {
    errorMessage = message;
    errorText.setString(message);

    sf::FloatRect errorBounds = errorText.getLocalBounds();
    errorText.setPosition({
      x + (width - errorBounds.size.x) / 2,
      y + headerHeight + 20
    });

    isLoading = false;
    hasError = true;
  }

  void draw(sf::RenderWindow& window) {
    // Draw headers
    for (auto& cell : headers) {
      cell.draw(window);
    }

    if (isLoading) {
      // Draw loading message
      window.draw(loadingText);
    } else if (hasError) {
      // Draw error message
      window.draw(errorText);
    } else {
      // Draw rows
      for (auto& row : rows) {
        for (auto& cell : row) {
          cell.draw(window);
        }
      }
    }
  }

private:
  sf::Font& font;
  float x, y, width, height;
  std::vector<TableCell> headers;
  std::vector<std::vector<TableCell>> rows;

  const float headerHeight = 30;
  const float rowHeight = 25;

  bool isLoading = true;
  bool hasError = false;
  std::string errorMessage;

  sf::Text loadingText;
  sf::Text errorText;
};

// Function to send DELETE request to delete a user
bool deleteUser(int userId, const std::string& token) {
  sf::Http http("localhost", 8000);
  sf::Http::Request request;
  request.setMethod(sf::Http::Request::Method::Delete);
  request.setUri("/api/users/" + std::to_string(userId));
  request.setHttpVersion(1, 1);
  request.setField("Authorization", "Bearer " + token);

  sf::Http::Response httpResponse = http.sendRequest(request);
  return httpResponse.getStatus() == sf::Http::Response::Status::NoContent;
}

void displayUsersTable(sf::RenderWindow& window, sf::Font& font, const std::vector<User>& users, float scrollOffset, const std::function<void(int)>& onDelete) {
  const float tableX = 50.0f;
  const float tableY = 100.0f; // Base table position
  const float rowHeight = 30.0f;
  const float colWidths[] = {50.0f, 200.0f, 100.0f, 100.0f}; // ID, Email, Role, Delete Button

  // Draw table headers
  sf::Text headerText(font);
  headerText.setCharacterSize(18);
  headerText.setFillColor(sf::Color::Black);

  const std::string headers[] = {"ID", "Email", "Role", "Actions"};
  float currentX = tableX;
  for (int i = 0; i < 4; ++i) {
    headerText.setString(headers[i]);
    headerText.setPosition({currentX, tableY});
    window.draw(headerText);
    currentX += colWidths[i];
  }

  // Draw table rows
  sf::Text rowText(font);
  rowText.setCharacterSize(16);
  rowText.setFillColor(sf::Color::Black);

  sf::RectangleShape deleteButton;
  deleteButton.setSize({80.0f, 20.0f});
  deleteButton.setFillColor(sf::Color::Red);

  sf::Text deleteButtonText(font);
  deleteButtonText.setCharacterSize(14);
  deleteButtonText.setFillColor(sf::Color::White);
  deleteButtonText.setString("Delete");

  for (size_t i = 0; i < users.size(); ++i) {
    const User& user = users[i];
    currentX = tableX;

    float rowY = tableY + (i + 1) * rowHeight - scrollOffset; // Apply scroll offset

    // Skip rows that are outside the visible area
    if (rowY + rowHeight < tableY || rowY > window.getSize().y) {
      continue;
    }

    rowText.setString(std::to_string(user.id));
    rowText.setPosition({currentX, rowY});
    window.draw(rowText);
    currentX += colWidths[0];

    rowText.setString(user.email);
    rowText.setPosition({currentX, rowY});
    window.draw(rowText);
    currentX += colWidths[1];

    std::string role = (user.roleId == 2) ? "Admin" : "User";
    rowText.setString(role);
    rowText.setPosition({currentX, rowY});
    window.draw(rowText);
    currentX += colWidths[2];

    // Draw delete button
    deleteButton.setPosition({currentX, rowY});
    window.draw(deleteButton);

    sf::FloatRect buttonBounds = deleteButton.getGlobalBounds();
    deleteButtonText.setPosition({currentX + (buttonBounds.size.x - deleteButtonText.getLocalBounds().size.x) / 2,
                                  rowY + (buttonBounds.size.y - deleteButtonText.getLocalBounds().size.y) / 2 - 5});
    window.draw(deleteButtonText);

    // Check for mouse click on delete button
    if (sf::Mouse::isButtonPressed(sf::Mouse::Button::Left)) {
      sf::Vector2f mousePos = window.mapPixelToCoords(sf::Mouse::getPosition(window));
      if (buttonBounds.contains(mousePos)) {
        onDelete(user.id);
      }
    }
  }
}


void setupHomeUI(sf::Font &font, sf::Text &welcomeText, sf::Text &userInfoText) {
  // Setup welcome text
  welcomeText.setFont(font);
  welcomeText.setString("Welcome to EventX Admin Dashboard");
  welcomeText.setCharacterSize(30);
  welcomeText.setFillColor(sf::Color::Black);
  welcomeText.setStyle(sf::Text::Bold);

  // Center the welcome text
  sf::FloatRect welcomeBounds = welcomeText.getLocalBounds();
  welcomeText.setPosition({
    (800 - welcomeBounds.size.x) / 2,
    30
  });
}

void setupAccessDeniedUI(sf::Font &font, sf::Text &titleText, sf::Text &messageText, sf::RectangleShape &backButton, sf::Text &backButtonText) {
  // Setup title text
  titleText.setFont(font);
  titleText.setString("Access Denied");
  titleText.setCharacterSize(36);
  titleText.setFillColor(sf::Color::Red);
  titleText.setStyle(sf::Text::Bold);

  // Center the title text
  sf::FloatRect titleBounds = titleText.getLocalBounds();
  titleText.setPosition({
    (800 - titleBounds.size.x) / 2,
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
    (800 - messageBounds.size.x) / 2,
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
}int main()
{
  sf::RenderWindow window(sf::VideoMode({800, 600}), "EventX Internal App");
  window.setFramerateLimit(60);

  sf::Font font;
  if (!font.openFromFile("../src/assets/fonts/Roboto/static/Roboto-Regular.ttf"))
  {
    std::cerr << "Failed to load font\n";
    return -1;
  }

  // Current app page
  AppPage currentPage = AppPage::Login;

  // Login page elements
  sf::Text emailLabel(font), emailText(font), passwordLabel(font), passwordText(font), loginButtonText(font), statusText(font);
  std::string emailInput, passwordInput;
  sf::RectangleShape emailBox, passwordBox, loginButton;

  // Home page elements
  sf::Text welcomeText(font), userInfoText(font);

  // Initial setup for login page
  setupLoginUI(font, emailLabel, emailBox, emailText, passwordLabel, passwordBox, passwordText, loginButton, loginButtonText);

  // Initial setup for home page
  setupHomeUI(font, welcomeText, userInfoText);

  // Status text for login feedback
  statusText.setFont(font);
  statusText.setString("");
  statusText.setCharacterSize(18);
  statusText.setPosition({300, 380});
  statusText.setFillColor(sf::Color::Red);

  bool isEmailFieldActive = false;
  bool isPasswordFieldActive = false;

  // Authentication data
  std::optional<AuthData> authDataOpt;

  // Animation variables for page transition
  float transitionAlpha = 0.0f; // 0 = login page, 1 = home page
  bool isTransitioning = false;
  sf::Clock transitionClock;
  const float transitionDuration = 0.5f; // seconds

  float scrollOffset = 0.0f;
  const float scrollSpeed = 30.0f; // Adjust scroll speed

  while (window.isOpen())
  {
    // Process events
    std::optional<sf::Event> eventOpt = window.pollEvent();
    while (eventOpt.has_value())
    {
      const sf::Event& event = eventOpt.value();

      // Use event handling with getIf
      if (event.is<sf::Event::Closed>())
      {
        window.close();
      }
      else if (currentPage == AppPage::Login && !isTransitioning)
      {
        if (const auto* mousePressed = event.getIf<sf::Event::MouseButtonPressed>())
        {
          sf::Vector2f mousePos = window.mapPixelToCoords(sf::Mouse::getPosition(window));

          // Check if email field clicked
          if (emailBox.getGlobalBounds().contains(mousePos))
          {
            isEmailFieldActive = true;
            isPasswordFieldActive = false;
            emailBox.setOutlineThickness(2);
            emailBox.setOutlineColor(sf::Color::Blue);
            passwordBox.setOutlineThickness(0);
          }
          // Check if password field clicked
          else if (passwordBox.getGlobalBounds().contains(mousePos))
          {
            isPasswordFieldActive = true;
            isEmailFieldActive = false;
            passwordBox.setOutlineThickness(2);
            passwordBox.setOutlineColor(sf::Color::Blue);
            emailBox.setOutlineThickness(0);
          }
          // Check if login button clicked
          else if (loginButton.getGlobalBounds().contains(mousePos))
          {
            // Simple validation
            if (emailInput.empty() || passwordInput.empty())
            {
              statusText.setString("Please fill in all fields");
              statusText.setFillColor(sf::Color::Red);
            }
            else
            {
              // Set status to "Logging in..."
              statusText.setString("Logging in...");
              statusText.setFillColor(sf::Color::Blue);

              // Need to display the "Logging in..." message before making the request
              window.clear(sf::Color::White);
              window.draw(emailLabel);
              window.draw(emailBox);
              window.draw(emailText);
              window.draw(passwordLabel);
              window.draw(passwordBox);
              window.draw(passwordText);
              window.draw(loginButton);
              window.draw(loginButtonText);
              window.draw(statusText);
              window.display();

              // Send login request to API
              std::string apiResponse;
              authDataOpt = sendLoginRequest(emailInput, passwordInput, apiResponse);

              if (authDataOpt.has_value()) {
                const auto& authData = authDataOpt.value();
                statusText.setString("Login successful!\nToken: " + authData.token + "\nRole ID: " + std::to_string(authData.roleId));
                statusText.setFillColor(sf::Color::Green);

                // Start transition to home page
                isTransitioning = true;
                transitionClock.restart();
              } else {
                statusText.setString(apiResponse);
                statusText.setFillColor(sf::Color::Red);
              }
            }
          }
          else
          {
            isEmailFieldActive = false;
            isPasswordFieldActive = false;
            emailBox.setOutlineThickness(0);
            passwordBox.setOutlineThickness(0);
          }
        }
        else if (const auto* textEntered = event.getIf<sf::Event::TextEntered>())
        {
          // Handle text input for active field
          if (isEmailFieldActive)
          {
            if (textEntered->unicode == 8 && !emailInput.empty()) // Backspace
            {
              emailInput.pop_back();
            }
            else if (textEntered->unicode >= 32 && textEntered->unicode < 128) // Printable ASCII
            {
              emailInput += static_cast<char>(textEntered->unicode);
            }
            emailText.setString(emailInput);
          }
          else if (isPasswordFieldActive)
          {
            if (textEntered->unicode == 8 && !passwordInput.empty()) // Backspace
            {
              passwordInput.pop_back();
            }
            else if (textEntered->unicode >= 32 && textEntered->unicode < 128) // Printable ASCII
            {
              passwordInput += static_cast<char>(textEntered->unicode);
            }
            // Show asterisks for password
            std::string passwordDisplay(passwordInput.length(), '*');
            passwordText.setString(passwordDisplay);
          }
        }
      }

      // Get next event
      eventOpt = window.pollEvent();
    }

    // Update transition if needed
    if (isTransitioning) {
      float elapsed = transitionClock.getElapsedTime().asSeconds();
      transitionAlpha = elapsed / transitionDuration;

      if (transitionAlpha >= 1.0f) {
        transitionAlpha = 1.0f;
        isTransitioning = false;
        currentPage = AppPage::Home;
      }
    }

    window.clear(sf::Color::White);

    // Draw UI elements based on current page
    if (currentPage == AppPage::Login && transitionAlpha < 1.0f) {
      // Apply fade-out effect during transition
      uint8_t opacity = static_cast<uint8_t>(255 * (1.0f - transitionAlpha));

      // Draw login UI elements with fading
      sf::Color textColor = sf::Color(0, 0, 0, opacity);
      emailLabel.setFillColor(textColor);
      emailText.setFillColor(textColor);
      passwordLabel.setFillColor(textColor);
      passwordText.setFillColor(textColor);
      loginButtonText.setFillColor(sf::Color(255, 255, 255, opacity));
      statusText.setFillColor(sf::Color(0, 255, 0, opacity)); // Green for success

      sf::Color boxColor = sf::Color(240, 240, 240, opacity);
      emailBox.setFillColor(boxColor);
      passwordBox.setFillColor(boxColor);
      loginButton.setFillColor(sf::Color(0, 0, 0, opacity));

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

    if (currentPage == AppPage::Home || transitionAlpha > 0.0f) {
      // Apply fade-in effect during transition
      uint8_t opacity = static_cast<uint8_t>(255 * transitionAlpha);

      sf::Color textColor = sf::Color(0, 0, 0, opacity);
      welcomeText.setFillColor(textColor);
      userInfoText.setFillColor(textColor);

      window.draw(welcomeText);
      window.draw(userInfoText);
    }

    if (currentPage == AppPage::Home) {
      // Fetch users and display them in a table
      const auto& authData = authDataOpt.value();
      std::string errorMessage;
      auto usersOpt = fetchUsers(authData.token, errorMessage);

      if (usersOpt.has_value()) {
        displayUsersTable(window, font, usersOpt.value(), scrollOffset, [&](int userId) {
          if (deleteUser(userId, authData.token)) {
            std::cout << "User " << userId << " deleted successfully." << std::endl;
          } else {
            std::cerr << "Failed to delete user " << userId << std::endl;
          }
        });
      } else {
        sf::Text errorText(font);
        errorText.setCharacterSize(18);
        errorText.setFillColor(sf::Color::Red);
        errorText.setString("Failed to fetch users: " + errorMessage);
        errorText.setPosition({50.0f, 100.0f});
        window.draw(errorText);
      }
    }

    window.display();
  }

  return 0;
}
