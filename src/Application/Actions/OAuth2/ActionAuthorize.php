<?php

namespace App\Application\Actions\OAuth2;

use App\Application\Actions\Action;
use App\Domain\User\User;
use App\Infrastructure\Persistence\AccessToken\InMemoryAccessTokenRepository;
use App\Infrastructure\Persistence\AuthCode\InMemoryAuthCodeRepository;
use App\Infrastructure\Persistence\Client\InMemoryClientRepository;
use App\Infrastructure\Persistence\RefreshToken\InMemoryRefreshTokenRepository;
use App\Infrastructure\Persistence\Scope\InMemoryScopeRepository;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

class ActionAuthorize extends Action
{
    protected function action(): Response
    {
        $request = $this->request;
        $response = $this->response;

        // Init our repositories
        $clientRepository = new InMemoryClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new InMemoryScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new InMemoryAccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $authCodeRepository = new InMemoryAuthCodeRepository(); // instance of AuthCodeRepositoryInterface
        $refreshTokenRepository = new InMemoryRefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

        // Path to public and private keys
        $privateKey = 'file:///keys/private.key';
        $encryptionKey = 'file:///keys/public.key';

        // Setup the authorization server
        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        $grant = new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            new DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );

        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $server->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $server->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser(new User(1, 'bill.gates', 'Bill', 'Gates', '123456')); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $server->completeAuthorizationRequest($authRequest, $response);

        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);

        } catch (Exception $exception) {
            // Unknown exception
            $body = new Stream(fopen('php://temp', 'r+'));
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);

        }
    }
}