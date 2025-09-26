#pragma once
#include <string>

class User {
public:
    User() = default;
    User(int id, const std::string& email, int roleId);

    int getId() const;
    const std::string& getEmail() const;
    int getRoleId() const;
    std::string getRoleName() const;

private:
    int id{0};
    std::string email;
    int roleId{0};
};
