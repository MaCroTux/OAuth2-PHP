<?php

namespace App\Infrastructure\Persistence\AuthCode;

use App\Domain\AuthCode\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class InMemoryAuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var AuthCodeEntity[]
     */
    private $authCode = [];

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCodeEntity();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $this->authCode[$authCodeEntity->getIdentifier()] = $authCodeEntity;
    }

    public function revokeAuthCode($codeId)
    {
        unset($this->authCode[$codeId]);
    }

    public function isAuthCodeRevoked($codeId)
    {
        return array_key_exists($codeId, $this->authCode);
    }
}