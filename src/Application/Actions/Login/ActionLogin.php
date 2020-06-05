<?php

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use App\Application\Actions\Stream;
use Psr\Http\Message\ResponseInterface as Response;

class ActionLogin extends Action
{
    protected function action(): Response
    {
        $request = $this->request;
        $message = $request->getQueryParams()['message'] ?? null;

        $stream = new Stream('');

        if (null !== $message) {
            $message = base64_decode($message);
            $stream->write($message);
        }

        return $this->loadTemplate('login.html', $stream);
    }
}