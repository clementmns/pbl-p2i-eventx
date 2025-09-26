#include "User.h"

User::User(int id, const std::string& email, int roleId)
    : id(id), email(email), roleId(roleId) {}

int User::getId() const {
    return id;
}

const std::string& User::getEmail() const {
    return email;
}

int User::getRoleId() const {
    return roleId;
}

std::string User::getRoleName() const {
    return (roleId == 2) ? "Admin" : "User";
}
