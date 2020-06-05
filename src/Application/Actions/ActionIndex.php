<?php

namespace App\Application\Actions;

use App\Application\JwtValidate;
use App\Application\JwtValidateException;
use Psr\Http\Message\ResponseInterface as Response;

class ActionIndex extends Action
{
    protected function action(): Response
    {
        $request = $this->request;
        $message = $request->getQueryParams()['message'] ?? null;

        $response = $this->response;
        $cookies = $request->getCookieParams();
        $jwt = $cookies['jwt'] ?? null;

        $validate = true;
        $jwtError = '';

        try {
            $jsonValidate = new JwtValidate('file:///keys/public.key');
            $jsonValidate->__invoke($jwt ?? '');
        } catch (JwtValidateException $e) {
            $jwtError = $e->getMessage();
            $validate = false;
        }

        if (!empty($jwt) && false === $validate) {
            return $response->withBody(new Stream('Token not is correct: ' . $jwtError));
        }

        if ($jwt && true === $validate) {
            return $response->withBody(new Stream('You has been logged!'));
        }

        if (null !== $message) {
            $message = base64_decode($message);
            $stream = new Stream($message . '<br>');

            return $this->loadTemplate('index.html', $stream);
        }

        return $this->loadTemplate('index.html');
    }
}