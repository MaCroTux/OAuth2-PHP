<?php

namespace App\Application;

class ServerParameter
{
    public static function host(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    public static function httpHost(): string
    {
        $testDomain = [
            '127.0.0.1',
            'localhost'
        ];

        $scheme = $_SERVER['REQUEST_SCHEME'];

        if (!in_array($_SERVER['HTTP_HOST'], $testDomain)) {
            $scheme = 'https';
        }

        return $scheme . '://' .$_SERVER['HTTP_HOST'];
    }
}