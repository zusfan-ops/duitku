<?php

namespace App\Libraries;

/**
 * Holds the authenticated user id for the current API request.
 * Populated by the ApiAuthFilter during the "before" phase.
 */
class ApiAuth
{
    private static int $userId = 0;

    public static function set(int $id): void
    {
        self::$userId = $id;
    }

    public static function id(): int
    {
        return self::$userId;
    }

    public static function check(): bool
    {
        return self::$userId > 0;
    }

    public static function clear(): void
    {
        self::$userId = 0;
    }
}
