<?php
declare(strict_types=1);

use App\Application\Actions\ActionIndex;
use App\Application\Actions\Login\ActionLogin;
use App\Application\Actions\Login\ActionLoginAuthorize;
use App\Application\Actions\OAuth2\ActionAccessToken;
use App\Application\Actions\OAuth2\ActionAccessTokenClientCredential;
use App\Application\Actions\OAuth2\ActionAuthCode;
use App\Application\Actions\OAuth2\ActionAuthorize;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', ActionIndex::class);

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->post('/access_token_client_credential', ActionAccessTokenClientCredential::class);
    $app->post('/access_token', ActionAccessToken::class);

    $app->get('/authorize', ActionAuthorize::class);
    $app->get('/auth_code', ActionAuthCode::class);

    $app->get('/login', ActionLogin::class);
    $app->post('/login', ActionLoginAuthorize::class);
};
