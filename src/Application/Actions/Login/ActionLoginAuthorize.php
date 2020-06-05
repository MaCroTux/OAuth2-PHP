<?php

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use App\Application\CurlService;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ActionLoginAuthorize extends Action
{
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
        $serverParams = $request->getServerParams();
        $response = $this->response;
        $form = $request->getParsedBody();
        $userName = $form['username'];
        $password = $form['password'];

        if (empty($userName) || empty($password)) {
            return $response->withHeader('Location' , '/login?message='.base64_encode('User name or password not be empty'));
        }

        $tokens = $this->getTokenAccess($serverParams, $userName, $password);

        if (!isset($tokens['error'])) {
            $expireIn = time()+$tokens['expires_in'];
            setcookie('jwt',$tokens['access_token'], $expireIn, '/', $serverParams['HTTP_HOST']);
            setcookie('refresh',$tokens['refresh_token'], $expireIn + (30*24*30*30), '/', $serverParams['HTTP_HOST']);

            $token = (new Parser())->parse($tokens['access_token']);
            /** @var Claim $claim */
            $claim = $token->getClaims()['aud'];
            $clientId = $claim->getValue();

            $redirect = $this->clientRepository->getClientEntity($clientId)->getRedirectUri();

            return $response->withHeader('Location' , $redirect . '?access_token=' . $tokens['access_token'] . '&refresh_token=' . $tokens['refresh_token']);
        }

        return $response->withHeader('Location' , '/?message=' . base64_encode('User o password incorrect!'));
    }

    private function getTokenAccess(array $serverParams, string $userName, string $password): array
    {
        $domain = $serverParams['HTTP_ORIGIN'];
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