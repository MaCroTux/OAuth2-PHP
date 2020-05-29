<?php

namespace App\Infrastructure\Persistence\RefreshToken;

use App\Domain\RefreshToken\RefreshTokenEntity;
use Jajo\JSONDB;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class JsonFileRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private const JSON_FILE = 'RefreshToken.json';

    /** @var JSONDB */
    private $jsonDb;

    public function __construct(JSONDB $jsonDb)
    {
        $this->jsonDb = $jsonDb;
    }

    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $accessTokenEntity): void
    {
        $this->jsonDb->insert(self::JSON_FILE,
            [
                'id' => $accessTokenEntity->getIdentifier(),
                'access_token' => json_encode($accessTokenEntity->getAccessToken()),
                'expiry_date' => $accessTokenEntity->getExpiryDateTime()->getTimestamp(),
            ]
        );
    }

    public function revokeRefreshToken($tokenId): void
    {
        $this->jsonDb
            ->delete()
            ->from(self::JSON_FILE)
            ->where(['id' => $tokenId]);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        $access = $this->jsonDb
            ->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $tokenId])
            ->get();

        return count($access) === 0;
    }
}