<?php

namespace App\Application\Actions\OAuth2;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

class ActionAuthCode extends Action
{
    private const URL_ACCESS_TOKEN = 'http://localhost/access_token';

    protected function action(): Response
    {
        $request = $this->request;
        $serverParams = $request->getServerParams();
        $response = $this->response;
        $parseRequest =$this->request->getQueryParams();

        $code = $parseRequest['code'] ?? '';

        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => 'code',
            'client_secret' => 'xxxx',
            'redirect_uri' => $serverParams['HTTP_ORIGIN'] . '/auth_code',
            'code' => $code,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::URL_ACCESS_TOKEN);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $tuData = curl_exec($curl);
        if(!curl_errno($curl)){
            $info = curl_getinfo($curl);
            echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
        } else {
            echo 'Curl error: ' . curl_error($curl);
        }

        curl_close($curl);
        echo json_encode($tuData);

        return $response;
    }
}