<?php

namespace App\Infrastructure\Persistence\AuthCode;

use App\Domain\AuthCode\AuthCodeEntity;
use Jajo\JSONDB;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class JsonFileAuthCodeRepository implements AuthCodeRepositoryInterface
{
    private const JSON_FILE = 'AuthCode.json';

    /** @var JSONDB */
    private $jsonDb;

    public function __construct(JSONDB $jsonDb)
    {
        $this->jsonDb = $jsonDb;
    }

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCodeEntity();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $this->jsonDb->insert(self::JSON_FILE,
            [
                'id' => $authCodeEntity->getIdentifier(),
                'client' => $authCodeEntity->getClient()->getIdentifier(),
                'expiry_date' => $authCodeEntity->getExpiryDateTime()->getTimestamp(),
                'scope' => json_encode($authCodeEntity->getScopes()),
                'user_id' => json_encode($authCodeEntity->getUserIdentifier()),
                'redirect_url' => json_encode($authCodeEntity->getRedirectUri()),
            ]
        );
    }

    public function revokeAuthCode($codeId)
    {
        $this->jsonDb
            ->delete()
            ->from(self::JSON_FILE)
            ->where(['id' => $codeId]);
    }

    public function isAuthCodeRevoked($codeId)
    {
        $access = $this->jsonDb
            ->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $codeId])
            ->get();

        return count($access) < 1;
    }
}