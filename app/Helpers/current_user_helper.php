<?php
// app/Helpers/current_user_helper.php

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Helper to get current user from Bearer token.
 * Depends on your jwt helper that has jwt_decode_token($token)
 */

if (! function_exists('get_bearer_token')) {
    function get_bearer_token(): ?string
    {
        $request = service('request'); // CodeIgniter request
        // Try Authorization header
        $header = $request->getServer('HTTP_AUTHORIZATION') ?? $request->getHeaderLine('Authorization');
        if (! $header) {
            return null;
        }
        if (strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        return null;
    }
}

if (! function_exists('current_jwt_payload')) {
    /**
     * Decode token and return decoded object (cached)
     *
     * @return object|null Decoded token object or null if invalid/missing
     */
    function current_jwt_payload(): ?object
    {
        static $cached = '__NOT_SET__';

        if ($cached !== '__NOT_SET__') {
            return $cached;
        }

        // Ensure jwt helper loaded
        helper('jwt');

        $token = get_bearer_token();
        if (! $token) {
            $cached = null;
            return null;
        }

        try {
            $decoded = jwt_decode_token($token); // your existing helper
            // decoded likely has ->data or direct claims depending on implementation
            $cached = $decoded;
            return $cached;
        } catch (\Throwable $e) {
            // invalid / expired token
            log_message('debug', 'current_jwt_payload: token decode failed: ' . $e->getMessage());
            $cached = null;
            return null;
        }
    }
}

if (! function_exists('current_user')) {
    /**
     * Return the decoded user object (usually $decoded->data)
     * or null
     */
    function current_user(): ?object
    {
        $payload = current_jwt_payload();
        if (! $payload) return null;

        // many jwt implementations store user info in ->data
        if (isset($payload->data) && is_object($payload->data)) {
            return $payload->data;
        }

        // otherwise assume claims are top-level
        return $payload;
    }
}

if (! function_exists('current_user_id')) {
    /**
     * Return user id if present (int) or null
     */
    function current_user_id(): ?int
    {
        $user = current_user();
        if (! $user) return null;

        if (isset($user->id)) return (int) $user->id;
        if (isset($user->user_id)) return (int) $user->user_id;
        return null;
    }
}
