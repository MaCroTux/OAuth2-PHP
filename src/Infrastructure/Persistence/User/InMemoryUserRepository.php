<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class InMemoryUserRepository implements UserRepository, UserRepositoryInterface
{
    /**
     * @var User[]
     */
    private $users;

    /**
     * InMemoryUserRepository constructor.
     *
     * @param array|null $users
     */
    public function __construct(array $users = null)
    {
        $this->users = $users ?? [
            1 => new User(1, 'bill.gates', 'Bill', 'Gates', '123456'),
            2 => new User(2, 'steve.jobs', 'Steve', 'Jobs', '123456'),
            3 => new User(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg', '123456'),
            4 => new User(4, 'evan.spiegel', 'Evan', 'Spiegel', '123456'),
            5 => new User(5, 'jack.dorsey', 'Jack', 'Dorsey', '123456'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->users);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfId(int $id): User
    {
        if (!isset($this->users[$id])) {
            throw new UserNotFoundException();
        }

        return $this->users[$id];
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {

        $users = array_filter($this->users, static function(User $user) use ($username, $password) {
            return $user->getUsername() === $username && $user->checkPass($password);
        });

        return array_shift($users);
    }
}
