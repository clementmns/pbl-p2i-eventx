#include "ApiService.h"
#include <SFML/Network.hpp>
#include <iostream>
#include <sstream>

ApiService::ApiService(const std::string& host, unsigned short port)
    : host(host), port(port) {}

std::optional<AuthData> ApiService::login(const std::string& email, const std::string& password, std::string& errorMessage) {
    sf::Http http(host, port);
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
        return parseLoginResponse(responseBody);
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

std::optional<std::vector<User>> ApiService::fetchUsers(const std::string& token, std::string& errorMessage) {
    sf::Http http(host, port);
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
}

bool ApiService::deleteUser(int userId, const std::string& token) {
    sf::Http http(host, port);
    sf::Http::Request request;
    request.setMethod(sf::Http::Request::Method::Delete);
    request.setUri("/api/users/" + std::to_string(userId));
    request.setHttpVersion(1, 1);
    request.setField("Authorization", "Bearer " + token);

    sf::Http::Response httpResponse = http.sendRequest(request);
    return httpResponse.getStatus() == sf::Http::Response::Status::Ok;
}

std::string ApiService::extractJsonValue(const std::string& json, const std::string& key) {
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

std::optional<AuthData> ApiService::parseLoginResponse(const std::string& response) {
    try {
        std::string token = extractJsonValue(response, "token");
        std::string roleIdStr = extractJsonValue(response, "roleId");
        std::string username = extractJsonValue(response, "username");

        int roleId = -1;
        if (!roleIdStr.empty() && std::all_of(roleIdStr.begin(), roleIdStr.end(), ::isdigit)) {
            roleId = std::stoi(roleIdStr);
        }

        if (token.empty()) {
            std::cerr << "Failed to parse token from response: " << response << std::endl;
            return std::nullopt;
        }

        return AuthData(token, roleId, username);
    } catch (const std::exception& e) {
        std::cerr << "Error parsing login response: " << e.what() << std::endl;
        return std::nullopt;
    }
}

std::vector<User> ApiService::parseUsersResponse(const std::string& response) {
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
                try {
                    std::string idStr = extractJsonValue(currentObj, "id");
                    int id = std::stoi(idStr);

                    std::string email = extractJsonValue(currentObj, "mail");

                    std::string roleIdStr = extractJsonValue(currentObj, "roleId");
                    int roleId = std::stoi(roleIdStr);

                    users.emplace_back(id, email, roleId);
                } catch (const std::exception& e) {
                    std::cerr << "Error parsing user object: " << e.what() << std::endl;
                }
            }
        }
    }

    return users;
}
