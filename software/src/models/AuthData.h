#pragma once
#include <string>

class AuthData {
public:
    AuthData() = default;
    AuthData(const std::string& token, int roleId, const std::string& username);

    const std::string& getToken() const;
    int getRoleId() const;
    const std::string& getUsername() const;
    bool isAdmin() const;

private:
    std::string token;
    int roleId{0};
    std::string username;
};
