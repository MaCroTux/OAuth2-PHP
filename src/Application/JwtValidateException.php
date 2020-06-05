<?php

namespace App\Application;

use Exception;
use InvalidArgumentException;

class JwtValidateException extends Exception
{
    private const NOT_BE_VERIFIED = 'Access token could not be verified';
    private const NOT_SINGED = 'Access token is not signed';
    private const TOKEN_IS_INVALID = 'Access token is invalid';
    private const JSON_ERROR = 'Error while decoding to JSON';

    public static function notBeVerified(): self
    {
        return new self(self::NOT_BE_VERIFIED);
    }

    public static function notSigned(): self
    {
        return new self(self::NOT_SINGED);
    }

    public static function tokenIsInvalid(): self
    {
        return new self(self::TOKEN_IS_INVALID);
    }

    public static function jsonError(): self
    {
        return new self(self::JSON_ERROR);
    }

    public static function fromInvalidArgumentException(InvalidArgumentException $invalidArgumentException): self
    {
        return new self($invalidArgumentException->getMessage());
    }
}