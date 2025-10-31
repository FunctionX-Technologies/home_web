<?php
// app/Helpers/jwt_helper.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (! function_exists('jwt_create_token')) {
    function jwt_create_token(array $userPayload): string
    {
        $secret = getenv('JWT_SECRET');
        $algo = getenv('JWT_ALGO') ?: 'HS256';
        $expire = (int) getenv('JWT_EXPIRE_SECONDS') ?: 3600;

        $issuedAt = time();
        $payload = [
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $issuedAt + $expire,
            // custom user data
            'data' => $userPayload
        ];

        return JWT::encode($payload, $secret, $algo);
    }
}

if (! function_exists('jwt_decode_token')) {
    /**
     * @throws \Exception on invalid/expired token
     */
    function jwt_decode_token(string $token): object
    {
        $secret = getenv('JWT_SECRET');
        $algo = getenv('JWT_ALGO') ?: 'HS256';
        return JWT::decode($token, new Key($secret, $algo));
    }
}
