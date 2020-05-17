<?php

namespace App\Application\Actions\OAuth2;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

class ActionAuthCode extends Action
{
    protected function action(): Response
    {
        $response = $this->response;
        $parseRequest =$this->request->getQueryParams();

        $code = $parseRequest['code'] ?? '';

        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => 'code',
            'client_secret' => 'xxxx',
            'redirect_uri' => 'http://127.0.0.1/auth_code',
            'code' => $code,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost/access_token");
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