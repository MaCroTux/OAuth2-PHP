<?php

namespace App\Application;

use App\Application\JwtValidateException;
use BadMethodCallException;
use InvalidArgumentException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\CryptKey;
use RuntimeException;

class JwtValidate
{
    /** @var string */
    private $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * @param string $jwt
     * @return void
     * @throws JwtValidateException
     */
    public function __invoke(string $jwt): void
    {
        $publicKey = new CryptKey($this->encryptionKey);

        try {
            // Attempt to parse and validate the JWT
            $token = (new Parser())->parse($jwt);
            try {
                if ($token->verify(new Sha256(), $publicKey->getKeyPath()) === false) {
                    throw JwtValidateException::notBeVerified();
                }
            } catch (BadMethodCallException $exception) {
                throw JwtValidateException::notSigned();
            }

            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(\time());

            if ($token->validate($data) === false) {
                throw JwtValidateException::tokenIsInvalid();
            }
        } catch (InvalidArgumentException $exception) {
            throw JwtValidateException::fromInvalidArgumentException($exception);
        } catch (RuntimeException $exception) {
            throw JwtValidateException::jsonError();
        }
    }
}