<?php
declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\App;

return static function (App $app) {
    $app->add(SessionMiddleware::class);
};
