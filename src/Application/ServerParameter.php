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
        return $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST'];
    }
}