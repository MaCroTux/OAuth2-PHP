<?php

namespace App\Domain\Client;

use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    /** @var string */
    private $identifier;
    /** @var string */
    private $name;
    /** @var string */
    private $redirectUri;
    /** @var bool */
    private $confidential;

    public function __construct(
        string $identifier,
        string $name,
        string $redirectUri,
        bool $confidential
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->redirectUri = $redirectUri;
        $this->confidential = $confidential;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }
}