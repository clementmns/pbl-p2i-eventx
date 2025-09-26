#pragma once
#include <string>
#include <vector>
#include <optional>
#include <functional>
#include "../models/User.h"
#include "../models/AuthData.h"

// Utility class to handle API communication
class ApiService {
public:
    ApiService(const std::string& host = "localhost", unsigned short port = 8000);

    // Authentication methods
    std::optional<AuthData> login(const std::string& email, const std::string& password, std::string& errorMessage);

    // User management methods
    std::optional<std::vector<User>> fetchUsers(const std::string& token, std::string& errorMessage);
    bool deleteUser(int userId, const std::string& token);

private:
    // JSON parsing helpers
    std::string extractJsonValue(const std::string& json, const std::string& key);
    std::optional<AuthData> parseLoginResponse(const std::string& response);
    std::vector<User> parseUsersResponse(const std::string& response);

    std::string host;
    unsigned short port;
};
