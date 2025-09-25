<?php
namespace App\Utils;

class Auth
{
    /**
     * Generates a JWT token for a given payload (user data).
     * @param array $payload
     * @param string|null $secret
     * @param int $expireSeconds
     * @return string
     */
    public static function generateJWT(array $payload, ?string $secret = null, int $expireSeconds = 86400): string
    {
        $secret = $secret ?: (getenv('JWT_SECRET') ?: 'your_jwt_secret');
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload['exp'] = time() + $expireSeconds;
        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload))
        ];
        $signingInput = implode('.', $segments);
        $signature = self::base64UrlEncode(hash_hmac('sha256', $signingInput, $secret, true));
        return $signingInput . '.' . $signature;
    }

    private static function base64UrlEncode($input)
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    /**
     * Extracts and validates a Bearer token from the Authorization header.
     * Returns the decoded payload if valid, or null if invalid/missing.
     */
    public static function getBearerTokenPayload(): ?array
    {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $secret = getenv('JWT_SECRET');
            $payload = self::decodeJWT($token, $secret);
            return $payload;
        }
        return null;
    }

    /**
     * Checks if the authenticated user is an admin.
     *
     * @return bool True if user is admin, false otherwise
     */
    public static function isAdmin(): bool
    {
        $payload = self::getBearerTokenPayload();
        if (!$payload || !isset($payload['id'])) {
            return false;
        }

        // Admin role ID is 2 according to the database seed
        return isset($payload['roleId']) && $payload['roleId'] === 2;
    }

    /**
     * Checks if the authenticated user has a specific role.
     *
     * @param int|array $roleIds Role ID or array of role IDs to check
     * @return bool True if user has any of the specified roles, false otherwise
     */
    public static function hasRole($roleIds): bool
    {
        $payload = self::getBearerTokenPayload();
        if (!$payload || !isset($payload['id']) || !isset($payload['roleId'])) {
            return false;
        }

        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];
        return in_array($payload['roleId'], $roleIds, true);
    }

    /**
     * Decodes a JWT token and verifies its signature.
     * Returns payload array if valid, null otherwise.
     */
    public static function decodeJWT(string $jwt, string $secret): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3)
            return null;
        list($header, $payload, $signature) = $parts;
        $header = json_decode(self::base64UrlDecode($header), true);
        $payload = json_decode(self::base64UrlDecode($payload), true);
        $valid = self::verifyJWTSignature($jwt, $secret);
        if ($valid)
            return $payload;
        return null;
    }

    private static function base64UrlDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    private static function verifyJWTSignature($jwt, $secret)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3)
            return false;
        list($header, $payload, $signature) = $parts;
        $data = "$header.$payload";
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', $data, $secret, true)), '+/', '-_'), '=');
        return hash_equals($expected, $signature);
    }
}
