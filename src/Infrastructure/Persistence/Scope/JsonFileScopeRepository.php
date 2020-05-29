<?php

namespace App\Infrastructure\Persistence\Scope;

use App\Domain\Scope\ScopeEntity;
use App\Domain\User\UserNotFoundException;
use Jajo\JSONDB;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class JsonFileScopeRepository implements ScopeRepositoryInterface
{
    private const JSON_FILE = 'Scope.json';

    /** @var JSONDB */
    private $jsonDb;

    public function __construct(JSONDB $jsonDb)
    {
        $this->jsonDb = $jsonDb;
        $this->provisioning();
    }

    public function provisioning()
    {
        $empty = new ScopeEntity('');
        $admin = new ScopeEntity('admin');
        $user = new ScopeEntity('user');
        $guest = new ScopeEntity('guest');

        $this->addDefaultScope($empty);
        $this->addDefaultScope($admin);
        $this->addDefaultScope($user);
        $this->addDefaultScope($guest);
    }

    private function addDefaultScope(ScopeEntity $entity)
    {
        $scopeStorage = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $entity->getIdentifier()])
            ->get();

        if (count($scopeStorage) === 0) {
            $this->jsonDb->insert(self::JSON_FILE, $entity->toArray());
        }
    }

    private function ofId(string $clientIdentifier): ScopeEntityInterface
    {
        $scopes = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $clientIdentifier])
            ->get();

        if (count($scopes) === 0) {
            throw new UserNotFoundException();
        }
        $scope = array_shift($scopes);

        return ScopeEntity::fromArray($scope);
    }

    /**
     * @param string $identifier
     * @return ScopeEntityInterface
     * @throws UserNotFoundException
     */
    public function getScopeEntityByIdentifier($identifier): ScopeEntityInterface
    {
        return $this->ofId($identifier);
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @param null $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        return $scopes;
    }
}