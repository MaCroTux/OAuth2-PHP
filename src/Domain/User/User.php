<?php
declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements JsonSerializable, UserEntityInterface
{
    /** @var int|null */
    private $id;
    /** @var string */
    private $username;
    /** @var string */
    private $firstName;
    /** @var string */
    private $lastName;
    /** @var string */
    private $pass;

    /**
     * @param int|null  $id
     * @param string    $username
     * @param string    $firstName
     * @param string    $lastName
     */
    public function __construct(?int $id, string $username, string $firstName, string $lastName, string $pass)
    {
        $this->id = $id;
        $this->username = strtolower($username);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
        $this->pass = $pass;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }

    public function getIdentifier()
    {
        return $this->getId();
    }

    public function checkPass(string $pass): bool
    {
        return $this->pass === $pass;
    }
}
