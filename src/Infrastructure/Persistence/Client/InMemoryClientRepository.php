<?php

namespace App\Infrastructure\Persistence\Client;

use App\Domain\Client\ClientEntity;
use App\Domain\User\UserNotFoundException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class InMemoryClientRepository implements ClientRepositoryInterface
{
    /**
     * @var ClientEntity[]
     */
    private $clients;

    /**
     * InMemoryClientRepository constructor.
     *
     * @param array|null $clients
     */
    public function __construct(array $clients = null)
    {
        $this->clients = $clients ?? [
            'credential' => new ClientEntity('credential', 'xxxx', ['admin'], 'client_credentials', 'Debug', 'http://127.0.0.1', true),
            'code' => new ClientEntity('code', 'xxxx', ['admin'], 'password,authorization_code,refresh_token', 'Debug', 'http://127.0.0.1/auth_code', true),
        ];
    }

    public function ofId(string $clientIdentifier): ClientEntityInterface
    {
        if (!isset($this->clients[$clientIdentifier])) {
            throw new UserNotFoundException();
        }

        return $this->clients[$clientIdentifier];
    }

    public function getClientEntity($clientIdentifier): ClientEntityInterface
    {
        return $this->ofId($clientIdentifier)->getClient();
    }

    /**
     * @param string $clientIdentifier
     * @param string|null $clientSecret
     * @param string|null $grantType
     *
     * @return bool
     * @throws UserNotFoundException
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->ofId($clientIdentifier);
        $grantTypeList = explode(',', $client->grantType());

        return $client->secret() === $clientSecret && in_array($grantType, $grantTypeList, true);
    }
}