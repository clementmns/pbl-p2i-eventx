#include "HomeView.h"
#include <iostream>

// TableCell implementation
TableCell::TableCell(sf::Font& font, const std::string& text, float width, float height, bool isHeader)
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
    sf::FloatRect textBounds = textObj.getGlobalBounds();
    textObj.setPosition({
        (width - textBounds.size.x) / 2,
        (height - textBounds.size.y) / 2 - 4
    });
}

void TableCell::setPosition(float x, float y) {
    rect.setPosition({x, y});

    sf::Vector2f rectPos = rect.getPosition();
    sf::Vector2f textPos = textObj.getPosition();

    textObj.setPosition({
        rectPos.x + textPos.x,
        rectPos.y + textPos.y
    });
}

void TableCell::draw(sf::RenderWindow& window) {
    window.draw(rect);
    window.draw(textObj);
}

float TableCell::getWidth() const {
    return width;
}

float TableCell::getHeight() const {
    return height;
}

// This method is already defined in the header file
// sf::Vector2f TableCell::getPosition() const {
//     return rect.getPosition();
// }

// HomeView implementation
HomeView::HomeView(sf::RenderWindow& window, sf::Font& font)
    : View(window, font), loadingText(font), errorText(font), welcomeText(font), userInfoText(font) {
    setupUI();
}

void HomeView::draw() {
    // Always draw with full opacity, no transition effects
    sf::Color textColor = sf::Color::Black;
    welcomeText.setFillColor(textColor);
    userInfoText.setFillColor(textColor);

    // Draw welcome text and user info
    window.draw(welcomeText);
    window.draw(userInfoText);

    // Always draw the user table
    drawUserTable();
}

void HomeView::handleEvent(const sf::Event& event) {
    if (isTransitioning) {
        return;
    }

    // Handle mouse wheel for scrolling
    if (const auto* scrollEvent = event.getIf<sf::Event::MouseWheelScrolled>()) {
        if (scrollEvent->wheel == sf::Mouse::Wheel::Vertical) {
            // Invert the delta direction to match expected scroll behavior
            // (negative delta = scroll down, positive delta = scroll up)
            scrollOffset -= scrollEvent->delta * scrollSpeed;

            // Enforce minimum scroll (can't scroll above the top)
            if (scrollOffset < 0) {
                scrollOffset = 0;
            }

            // Calculate max scroll based on number of rows
            float tableHeight = headerHeight + rows.size() * rowHeight;
            float viewableHeight = window.getSize().y - (tableY + headerHeight);
            float maxScroll = std::max(0.0f, tableHeight - viewableHeight);

            // Enforce maximum scroll (can't scroll past the bottom)
            if (scrollOffset > maxScroll) {
                scrollOffset = maxScroll;
            }
        }
    }

        // Handle mouse clicks for delete buttons
    if (const auto* mouseEvent = event.getIf<sf::Event::MouseButtonPressed>()) {
        if (!isLoading && !hasError && mouseEvent->button == sf::Mouse::Button::Left) {
            sf::Vector2f mousePos = window.mapPixelToCoords(sf::Mouse::getPosition(window));

            // Check each row
            for (size_t i = 0; i < rows.size(); ++i) {
                // Calculate the position of the delete button in this row with scroll offset
                float rowY = tableY + headerHeight + (i * rowHeight) - scrollOffset;

                // Skip if row is not visible
                if (rowY + rowHeight < tableY + headerHeight || rowY > window.getSize().y) {
                    continue;
                }

                // Calculate the position of the delete button (last cell)
                float deleteButtonX = tableX;
                for (size_t j = 0; j < rows[i].size() - 1; ++j) {
                    deleteButtonX += headers[j].getWidth();
                }

                // Define button bounds for SFML 3.0.2 (position and size vectors)
                sf::FloatRect buttonBounds(
                    {deleteButtonX, rowY},
                    {headers.back().getWidth(), rowHeight}
                );

                // Check if button was clicked
                if (buttonBounds.contains(mousePos)) {
                    // Get user ID from the first cell in the row
                    int userId = std::stoi(rows[i][0].getText().getString().toAnsiString());
                    if (onDeleteUser) {
                        onDeleteUser(userId);
                    }
                    break;
                }
            }
        }
    }
}

void HomeView::update(float deltaTime) {
    // No transition effect needed
}

void HomeView::setUsers(const std::vector<User>& users) {
    rows.clear();
    scrollOffset = 0.0f; // Reset scroll position when loading new data

    if (users.empty()) {
        return;
    }

    // Calculate column widths - use the same widths as headers
    float idWidth = 50.0f;
    float emailWidth = 200.0f;
    float roleWidth = 100.0f;
    float actionWidth = 100.0f;

    float currentY = tableY + headerHeight; // Start positioning rows right below header

    for (const auto& user : users) {
        std::vector<TableCell> row;

        // Create cells for this row
        row.push_back(TableCell(font, std::to_string(user.getId()), idWidth, rowHeight));
        row.push_back(TableCell(font, user.getEmail(), emailWidth, rowHeight));
        row.push_back(TableCell(font, user.getRoleName(), roleWidth, rowHeight));
        row.push_back(TableCell(font, "Delete", actionWidth, rowHeight));

        // Set positions for cells in this row
        float currentX = tableX;
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

void HomeView::setError(const std::string& message) {
    errorMessage = message;
    errorText.setString(message);

    sf::FloatRect errorBounds = errorText.getLocalBounds();
    errorText.setPosition({
        tableX + (400 - errorBounds.size.x) / 2,
        tableY + headerHeight + 20
    });

    isLoading = false;
    hasError = true;
}

void HomeView::setWelcomeMessage(const std::string& message) {
    welcomeText.setString(message);

    // Center the welcome text
    sf::FloatRect welcomeBounds = welcomeText.getLocalBounds();
    welcomeText.setPosition({
        (window.getSize().x - welcomeBounds.size.x) / 2,
        30
    });
}

void HomeView::setUserInfo(const std::string& info) {
    userInfoText.setString(info);
    userInfoText.setPosition({tableX, 70});
}

void HomeView::startTransitionIn() {
    // Disable transitions for immediate display
    isTransitioning = false;
    transitionAlpha = 1.0f; // Full opacity immediately
}

void HomeView::setOnDeleteUser(std::function<void(int)> callback) {
    onDeleteUser = callback;
}

void HomeView::setupUI() {
    // Setup welcome text
    welcomeText.setFont(font);
    welcomeText.setString("Welcome to EventX Admin Dashboard");
    welcomeText.setCharacterSize(30);
    welcomeText.setFillColor(sf::Color::Black);
    welcomeText.setStyle(sf::Text::Bold);

    // Center the welcome text
    sf::FloatRect welcomeBounds = welcomeText.getLocalBounds();
    welcomeText.setPosition({
        (window.getSize().x - welcomeBounds.size.x) / 2,
        30
    });

    userInfoText.setFont(font);
    userInfoText.setString("");
    userInfoText.setCharacterSize(18);
    userInfoText.setFillColor(sf::Color::Black);
    userInfoText.setPosition({tableX, 70});

    // Create table headers
    float idWidth = 50.0f;
    float emailWidth = 200.0f;
    float roleWidth = 100.0f;
    float actionWidth = 100.0f;

    headers.push_back(TableCell(font, "ID", idWidth, headerHeight, true));
    headers.push_back(TableCell(font, "Email", emailWidth, headerHeight, true));
    headers.push_back(TableCell(font, "Role", roleWidth, headerHeight, true));
    headers.push_back(TableCell(font, "Actions", actionWidth, headerHeight, true));

    // Set header positions
    float currentX = tableX;
    for (auto& cell : headers) {
        cell.setPosition(currentX, tableY);
        currentX += cell.getWidth();
    }

    // Set loading text
    loadingText.setFont(font);
    loadingText.setString("Loading users...");
    loadingText.setCharacterSize(18);
    loadingText.setFillColor(sf::Color::Black);

    sf::FloatRect loadingBounds = loadingText.getLocalBounds();
    loadingText.setPosition({
        tableX + (400 - loadingBounds.size.x) / 2,
        tableY + headerHeight + 20
    });

    // Set error text
    errorText.setFont(font);
    errorText.setCharacterSize(16);
    errorText.setFillColor(sf::Color::Red);
}

void HomeView::drawUserTable() {
    // First, draw the fixed headers
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
        // Keep track of the original view
        sf::View originalView = window.getView();

        // Define the header boundary
        float headerBottom = tableY + headerHeight;

        // Draw rows - each row is positioned dynamically based on the scroll offset
        for (size_t i = 0; i < rows.size(); i++) {
            // Calculate the y-position with scroll offset
            float rowY = tableY + headerHeight + (i * rowHeight) - scrollOffset;

            // Skip rows that are not visible
            if (rowY + rowHeight < headerBottom || rowY > window.getSize().y) {
                continue;
            }

            // Process each cell in the row
            float cellX = tableX;

            for (size_t j = 0; j < rows[i].size(); j++) {
                // Create a rectangle for the cell background
                sf::RectangleShape cellRect;
                cellRect.setSize({headers[j].getWidth(), rowHeight});
                cellRect.setPosition({cellX, rowY});
                cellRect.setFillColor(sf::Color(240, 240, 240));
                cellRect.setOutlineThickness(1);
                cellRect.setOutlineColor(sf::Color(200, 200, 200));

                // Create text for the cell content
                sf::Text cellText(font);
                cellText.setString(rows[i][j].getText().getString());
                cellText.setCharacterSize(16);
                cellText.setFillColor(sf::Color::Black);

                // Position the text within the cell - centered horizontally and vertically
                sf::FloatRect textBounds = cellText.getLocalBounds();
                cellText.setPosition({
                    cellX + (headers[j].getWidth() - textBounds.size.x) / 2.0f,
                    rowY + (rowHeight - textBounds.size.y) / 2.0f - 4.0f
                });

                // Draw only if the cell would be visible below the header
                if (rowY >= headerBottom) {
                    window.draw(cellRect);
                    window.draw(cellText);
                }

                // Move to the next cell position
                cellX += headers[j].getWidth();
            }
        }

        // Restore the original view
        window.setView(originalView);
    }
}
