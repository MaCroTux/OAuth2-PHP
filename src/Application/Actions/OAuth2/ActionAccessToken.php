<?php

namespace App\Application\Actions\OAuth2;

use App\Application\Actions\Action;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Stream;

class ActionAccessToken extends Action
{
    /** @var ClientRepositoryInterface */
    private $clientRepository;
    /** @var ScopeRepositoryInterface */
    private $scopeRepository;
    /** @var AccessTokenRepositoryInterface */
    private $accessTokenRepository;
    /** @var AuthCodeRepositoryInterface */
    private $authCodeRepository;
    /** @var RefreshTokenRepositoryInterface */
    private $refreshTokenRepository;
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        LoggerInterface $logger,
        ClientRepositoryInterface $clientRepository,
        ScopeRepositoryInterface $scopeRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($logger);

        $this->clientRepository = $clientRepository;
        $this->scopeRepository = $scopeRepository;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->authCodeRepository = $authCodeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->userRepository = $userRepository;
    }

    protected function action(): Response
    {
        $request = $this->request;
        $response = $this->response;

        $parseBody =$request->getParsedBody();
        $grantType = $parseBody['grant_type'];

        // Path to public and private keys
        $privateKey = 'file:///keys/private.key';
        $encryptionKey = 'file:///keys/public.key';

        //$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
        //$encryptionKey = 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'; // generate using base64_encode(random_bytes(32))

        // Setup the authorization server
        $server = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            $privateKey,
            $encryptionKey
        );

        if ($grantType === 'password') {
            $grant = new PasswordGrant(
                $this->userRepository,
                $this->refreshTokenRepository
            );
        }

        if ($grantType === 'refresh_token') {
            $grant = new RefreshTokenGrant($this->refreshTokenRepository);
            $grant->setRefreshTokenTTL(new DateInterval('P1M')); // new refresh tokens will expire after 1 month
        }

        if ($grantType === 'authorization_code') {
            $grant = new AuthCodeGrant(
                $this->authCodeRepository,
                $this->refreshTokenRepository,
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