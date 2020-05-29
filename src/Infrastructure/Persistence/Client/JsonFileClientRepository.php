<?php

namespace App\Infrastructure\Persistence\Client;

use App\Domain\Client\ClientEntity;
use App\Domain\User\UserNotFoundException;
use Jajo\JSONDB;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class JsonFileClientRepository implements ClientRepositoryInterface
{
    private const JSON_FILE = 'Client.json';

    /** @var JSONDB */
    private $jsonDb;

    public function __construct(JSONDB $jsonDb)
    {
        $this->jsonDb = $jsonDb;
        $this->provisioning();
    }

    public function provisioning()
    {
        $clientDefault1 = new ClientEntity('credential', 'xxxx', ['admin'], 'client_credentials', 'Debug', 'http://127.0.0.1', true);
        $clientDefault2 = new ClientEntity('code', 'xxxx', ['admin'], 'password,authorization_code,refresh_token', 'Debug', 'http://127.0.0.1/auth_code', true);

        $this->addDefaultClient($clientDefault1);
        $this->addDefaultClient($clientDefault2);
    }

    private function addDefaultClient(ClientEntity $entity)
    {
        $entityStorage = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $entity->getIdentifier()])
            ->get();

        if (count($entityStorage) === 0) {
            $this->jsonDb->insert(self::JSON_FILE, $entity->toArray());
        }
    }

    /**
     * @param string $clientIdentifier
     * @return ClientEntityInterface
     * @throws UserNotFoundException
     */
    public function getClientEntity($clientIdentifier): ClientEntityInterface
    {
        return $this->getFromId($clientIdentifier);
    }

    private function getFromId($clientIdentifier): ClientEntityInterface
    {
        $clients = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $clientIdentifier])
            ->get();
        if (count($clients) === 0) {
            throw new UserNotFoundException();
        }
        $client = array_shift($clients);

        return ClientEntity::formArray($client);
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
        $client = $this->getFromId($clientIdentifier);
        $grantTypeList = explode(',', $client->grantType());

        return $client->secret() === $clientSecret && in_array($grantType, $grantTypeList, true);
    }
}