<?php

namespace App\Application\Actions\OAuth2;

use App\Application\Actions\Action;
use App\Infrastructure\Persistence\AccessToken\InMemoryAccessTokenRepository;
use App\Infrastructure\Persistence\AuthCode\InMemoryAuthCodeRepository;
use App\Infrastructure\Persistence\Client\InMemoryClientRepository;
use App\Infrastructure\Persistence\RefreshToken\InMemoryRefreshTokenRepository;
use App\Infrastructure\Persistence\Scope\InMemoryScopeRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

class ActionAccessToken extends Action
{
    protected function action(): Response
    {
        $request = $this->request;
        $response = $this->response;

        $parseBody =$request->getParsedBody();
        $grantType = $parseBody['grant_type'];

        // Init our repositories
        $clientRepository = new InMemoryClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new InMemoryScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new InMemoryAccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $authCodeRepository = new InMemoryAuthCodeRepository(); // instance of AuthCodeRepositoryInterface
        $refreshTokenRepository = new InMemoryRefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface
        $userRepository = new InMemoryUserRepository(); // instance of UserRepositoryInterface

        // Path to public and private keys
        $privateKey = 'file:///keys/private.key';
        $encryptionKey = 'file:///keys/public.key';

        //$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
        //$encryptionKey = 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'; // generate using base64_encode(random_bytes(32))

        // Setup the authorization server
        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        if ($grantType === 'password') {
            $grant = new PasswordGrant(
                $userRepository,
                $refreshTokenRepository
            );
        }

        if ($grantType === 'refresh_token') {
            $grant = new RefreshTokenGrant($refreshTokenRepository);
            $grant->setRefreshTokenTTL(new DateInterval('P1M')); // new refresh tokens will expire after 1 month
        }

        if ($grantType === 'authorization_code') {
            $grant = new AuthCodeGrant(
                $authCodeRepository,
                $refreshTokenRepository,
                new DateInterval('PT10M') // authorization codes will expire after 10 minutes
            );
        }

        // Enable the client credentials grant on the server
        $server->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        try {

            // Try to respond to the request
            return $server->respondToAccessTokenRequest($request, $response);

        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);

        } catch (Exception $exception) {
            // Unknown exception
            $body = new Stream('php://temp', 'r+');
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);

        }
    }
}