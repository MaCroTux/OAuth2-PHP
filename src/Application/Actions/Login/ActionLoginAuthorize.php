<?php

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use App\Application\Actions\Stream;
use Psr\Http\Message\ResponseInterface as Response;

class ActionLoginAuthorize extends Action
{
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

        $domain = $serverParams['HTTP_ORIGIN'];
        $oauthResponse = $this->sendCurl(
            $domain . '/access_token',
            [
                'grant_type' => 'password',
                'client_id' => 'code',
                'client_secret' => 'xxxx',
                'scope' => 'admin',
                'username' => $userName,
                'password' => $password,
            ]
        );

        $tokens = json_decode($oauthResponse, true);
        $expireIn = time()+$tokens['expires_in'];
        setcookie('jwt',$tokens['access_token'], $expireIn, '/', $serverParams['HTTP_HOST']);
        setcookie('refresh',$tokens['refresh_token'], $expireIn + (30*24*30*30), '/', $serverParams['HTTP_HOST']);

        return $response->withBody(new Stream('Login is success'));
    }

    private function sendCurl(string $url, array $data): string
    {
        //create a new cURL resource
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        //attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        //return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute the POST request
        $result = curl_exec($ch);

        //close cURL resource
        curl_close($ch);

        return $result;
    }
}