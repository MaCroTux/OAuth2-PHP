<?php

namespace App\Domain\AccessToken;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    public const ACCESS_TOKEN_TTL = 'PT1H';

    use AccessTokenTrait;
    use EntityTrait;
    use TokenEntityTrait;

    public function __construct(ClientEntityInterface $clientEntity, array $scopes, ?int $userIdentifier)
    {
        $dateTime = new \DateTimeImmutable('now');
        $dateTime->add(new \DateInterval(self::ACCESS_TOKEN_TTL));
        $this->setClient($clientEntity);
        $this->setIdentifier($clientEntity->getIdentifier());
        $this->setUserIdentifier($userIdentifier);
        $this->setExpiryDateTime($dateTime);

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }
    }
}