<?php

namespace App\Application\Actions\OAuth2;

use App\Application\Actions\Action;
use App\Infrastructure\Persistence\AccessToken\InMemoryAccessTokenRepository;
use App\Infrastructure\Persistence\Client\InMemoryClientRepository;
use App\Infrastructure\Persistence\Scope\InMemoryScopeRepository;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Stream;

class ActionAccessTokenClientCredential extends Action
{
    /** @var ClientRepositoryInterface */
    private $clientRepository;
    /** @var ScopeRepositoryInterface */
    private $scopeRepository;
    /** @var AccessTokenRepositoryInterface */
    private $accessTokenRepository;

    public function __construct(
        LoggerInterface $logger,
        ClientRepositoryInterface $clientRepository,
        ScopeRepositoryInterface $scopeRepository,
        AccessTokenRepositoryInterface $accessTokenRepository
    ) {
        parent::__construct($logger);

        $this->clientRepository = $clientRepository;
        $this->scopeRepository = $scopeRepository;
        $this->accessTokenRepository = $accessTokenRepository;
    }
    protected function action(): Response
    {
        $request = $this->request;
        $response = $this->response;

        // Init our repositories
        $this->clientRepository = new InMemoryClientRepository(); // instance of ClientRepositoryInterface
        $this->scopeRepository = new InMemoryScopeRepository(); // instance of ScopeRepositoryInterface
        $this->accessTokenRepository = new InMemoryAccessTokenRepository(); // instance of AccessTokenRepositoryInterface

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

        // Enable the client credentials grant on the server
        $server->enableGrantType(
            new ClientCredentialsGrant(),
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