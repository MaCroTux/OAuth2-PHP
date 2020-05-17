<?php

namespace App\Infrastructure\Persistence\AccessToken;

use App\Domain\AccessToken\AccessTokenEntity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class InMemoryAccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var AccessTokenEntityInterface[]
     */
    private $accessTokenEntity;

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        return new AccessTokenEntity($clientEntity, $scopes, $userIdentifier);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->accessTokenEntity[$accessTokenEntity->getIdentifier()] = $accessTokenEntity;
    }

    public function revokeAccessToken($tokenId): void
    {
        unset($this->accessTokenEntity[$tokenId]);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return array_key_exists($tokenId, $this->accessTokenEntity);
    }
}