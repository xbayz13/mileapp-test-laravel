<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Validate MongoDB ObjectId format
     *
     * @param string $id
     * @return bool
     */
    public static function isValidMongoObjectId(string $id): bool
    {
        return preg_match('/^[a-f\d]{24}$/i', $id) === 1;
    }
}
