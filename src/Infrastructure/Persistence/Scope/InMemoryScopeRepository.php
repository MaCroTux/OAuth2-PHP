<?php

namespace App\Infrastructure\Persistence\Scope;

use App\Domain\Scope\ScopeEntity;
use App\Domain\User\UserNotFoundException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class InMemoryScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var ScopeEntityInterface[]
     */
    private $scopes;

    /**
     * InMemoryScopeRepository constructor.
     *
     * @param array|null $scopes
     */
    public function __construct(array $scopes = null)
    {
        $this->scopes = $scopes ?? [
                '' => new ScopeEntity(''),
                'admin' => new ScopeEntity('admin'),
                'user' => new ScopeEntity('user'),
                'guest' => new ScopeEntity('guest'),
            ];
    }

    public function ofId(string $clientIdentifier): ScopeEntityInterface
    {
        if (!isset($this->scopes[$clientIdentifier])) {
            throw new UserNotFoundException();
        }
        return $this->scopes[$clientIdentifier];
    }
    /**
     * @param string $identifier The scope identifier
     *
     * @return ScopeEntityInterface|null
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