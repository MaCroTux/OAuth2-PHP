<?php

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use App\Application\CurlService;
use App\Application\ServerParameter;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Google_Client;

class ActionSocialAuthorize extends Action
{
    private const GOOGLE_API_KEY = '76181450114-f6n9vn157qspn85u7l3gslvf1hme0mtc.apps.googleusercontent.com';

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    public function __construct(LoggerInterface $logger, ClientRepositoryInterface $clientRepository)
    {
        parent::__construct($logger);

        $this->clientRepository = $clientRepository;
    }

    protected function action(): Response
    {
        $request = $this->request;
	    $response = $this->response;
	    $referer = $request->getQueryParams()['referrer'] ?? null;
        $params = $request->getParsedBody();
        $cookies = $request->getCookieParams();
        $accessToken = $params['credential'] ?? null;

        if (($cookies['g_csrf_token'] ?? '') !== ($params['g_csrf_token'] ?? '')) {
            die('CSRF Token not valid!');
        }

        $client = new Google_Client(['client_id' => self::GOOGLE_API_KEY]);

        $result = $client->verifyIdToken($accessToken);

        $user = $result['email'];
        $pass = $result['sub'];

        $tokens = $this->getTokenAccess(ServerParameter::httpHost(), $user, $pass);

        $domain = '.javierferia.com';

        if (!isset($tokens['error'])) {
            $expireIn = time() + $tokens['expires_in'];
            setcookie('jwt',$tokens['access_token'], $expireIn, '/', $domain);
            setcookie('refresh',$tokens['refresh_token'], $expireIn + (30*24*30*30), '/', $domain);

            $token = (new Parser())->parse($tokens['access_token']);

            /** @var Claim $claim */
            $claim = $token->getClaims()['aud'];
            $clientId = $claim->getValue();
            $redirect = $this->clientRepository->getClientEntity($clientId)->getRedirectUri();

            if (null !== $referer) {
                return $response->withHeader('Location' , $referer);
            }

            $redirect = empty($referer) ? $redirect : $referer;

            return $response->withHeader('Location' , $redirect . '?access_token=' . $tokens['access_token'] . '&refresh_token=' . $tokens['refresh_token']);
        }

        return $response;
    }	

    private function getTokenAccess(string $domain, string $userName, string $password): array
    {
        $curlService = new CurlService($domain);

        $oauthResponse = $curlService->__invoke(
            '/access_token',
            [
                'grant_type' => 'password',
                'client_id' => 'code',
                'client_secret' => 'xxxx',
                'scope' => 'admin',
                'username' => $userName,
                'password' => $password,
            ]
        );

        return json_decode($oauthResponse, true);
    }
}
