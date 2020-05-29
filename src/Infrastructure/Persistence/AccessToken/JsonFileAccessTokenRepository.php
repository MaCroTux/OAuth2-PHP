<?php

namespace App\Infrastructure\Persistence\AccessToken;

use App\Domain\AccessToken\AccessTokenEntity;
use Jajo\JSONDB;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class JsonFileAccessTokenRepository implements AccessTokenRepositoryInterface
{
    private const JSON_FILE = 'AccessToken.json';

    /** @var JSONDB */
    private $jsonDb;

    public function __construct(JSONDB $jsonDb)
    {
        $this->jsonDb = $jsonDb;
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        return new AccessTokenEntity($clientEntity, $scopes, $userIdentifier);
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->jsonDb->insert(self::JSON_FILE,
            [
                'id' => $accessTokenEntity->getIdentifier(),
                'client' => $accessTokenEntity->getClient()->getIdentifier(),
                'expiry_date' => $accessTokenEntity->getExpiryDateTime()->getTimestamp(),
                'scope' => json_encode($accessTokenEntity->getScopes()),
                'user_id' => json_encode($accessTokenEntity->getUserIdentifier()),
            ]
        );
    }

    public function revokeAccessToken($tokenId): void
    {
        $this->jsonDb
            ->delete()
            ->from(self::JSON_FILE)
            ->where(['id' => $tokenId]);
    }

    public function isAccessTokenRevoked($tokenId)
    {
        $access = $this->jsonDb
            ->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $tokenId])
            ->get();

        return count($access) === 0;
    }
}