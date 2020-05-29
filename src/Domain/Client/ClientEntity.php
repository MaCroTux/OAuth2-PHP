<?php

namespace App\Domain\Client;

use League\OAuth2\Server\Entities\ClientEntityInterface;

class ClientEntity implements ClientEntityInterface
{
    /** @var string */
    private $identifier;
    /** @var string */
    private $name;
    /** @var string */
    private $redirectUri;
    /** @var bool */
    private $confidential;
    /** @var string */
    private $secret;
    /** @var array */
    private $scope;
    /** @var string */
    private $grantType;

    public function __construct(
        string $identifier,
        string $secret,
        array $scope,
        string $grantType,
        string $name,
        string $redirectUri,
        bool $confidential
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->redirectUri = $redirectUri;
        $this->confidential = $confidential;
        $this->secret = $secret;
        $this->scope = $scope;
        $this->grantType = $grantType;
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

    public function secret(): string
    {
        return $this->secret;
    }

    public function scope(): array
    {
        return $this->scope;
    }

    public function grantType(): string
    {
        return $this->grantType;
    }

    public function getClient(): Client
    {
        return new Client(
            $this->identifier,
            $this->name,
            $this->redirectUri,
            $this->confidential
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->identifier,
            'name' => $this->name,
            'redirectUri' => $this->redirectUri,
            'confidential' => $this->confidential,
            'secret' => $this->secret,
            'scope' => json_encode($this->scope),
            'grantType' => $this->grantType,
        ];
    }

    public static function formArray(array $client): self
    {
        return new self(
            $client['id'],
            $client['secret'],
            json_decode($client['scope'], true),
            $client['grantType'],
            $client['name'],
            $client['redirectUri'],
            (bool)$client['confidential']
        );
    }
}