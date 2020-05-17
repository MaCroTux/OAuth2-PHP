<?php

namespace App\Infrastructure\Persistence\RefreshToken;

use App\Domain\RefreshToken\RefreshTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class InMemoryRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var RefreshTokenEntityInterface[]
     */
    private $refreshTokenEntity = [];

    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $accessTokenEntity): void
    {
        $this->refreshTokenEntity[$accessTokenEntity->getIdentifier()] = $accessTokenEntity;
    }

    public function revokeRefreshToken($tokenId): void
    {
        unset($this->refreshTokenEntity[$tokenId]);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return array_key_exists($tokenId, $this->refreshTokenEntity);
    }
}