#include "AuthData.h"

AuthData::AuthData(const std::string& token, int roleId, const std::string& username)
    : token(token), roleId(roleId), username(username) {}

const std::string& AuthData::getToken() const {
    return token;
}

int AuthData::getRoleId() const {
    return roleId;
}

const std::string& AuthData::getUsername() const {
    return username;
}

bool AuthData::isAdmin() const {
    return roleId == 2; // Assuming 2 is admin role ID
}
