<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Jajo\JSONDB;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class JsonFileUserRepository implements UserRepository, UserRepositoryInterface
{
    private const JSON_FILE = 'User.json';

    /** @var JSONDB */
    private $jsonDb;

    public function __construct(JSONDB $jsonDb)
    {
        $this->jsonDb = $jsonDb;
        $this->provisioning();
    }

    public function provisioning()
    {
        $user1 = new User(1, 'bill.gates', 'Bill', 'Gates', '123456');
        $user2 = new User(2, 'steve.jobs', 'Steve', 'Jobs', '123456');
        $user3 = new User(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg', '123456');
        $user4 = new User(4, 'evan.spiegel', 'Evan', 'Spiegel', '123456');
        $user5 = new User(5, 'jack.dorsey', 'Jack', 'Dorsey', '123456');

        $this->addDefaultUser($user1);
        $this->addDefaultUser($user2);
        $this->addDefaultUser($user3);
        $this->addDefaultUser($user4);
        $this->addDefaultUser($user5);
    }

    private function addDefaultUser(User $entity)
    {
        $emptyScope = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $entity->getIdentifier()])
            ->get();

        if (count($emptyScope) === 0) {
            $this->jsonDb->insert(self::JSON_FILE, $entity->toArray());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfId(int $id): User
    {
        $users = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['id' => $id])
            ->get();

        if (count($users) === 0) {
            throw new UserNotFoundException();
        }

        $user = array_shift($users);

        return User::fromArray($user);
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $users = $this->jsonDb->select('*')
            ->from(self::JSON_FILE)
            ->where(['username' => $username])
            ->get();

        $user = array_shift($users);

        $userEntity = User::fromArray($user);

        return $userEntity->checkPass($password) ? $userEntity : null ;
    }
}
