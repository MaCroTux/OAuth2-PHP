<?php

namespace App\Domain\Scope;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

class ScopeEntity implements ScopeEntityInterface
{
    /** @var string */
    private $identifier;

    public function __construct(
        string $identifier
    ) {
        $this->identifier = $identifier;
    }

    /**
     * Get the scope's identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function jsonSerialize(): string
    {
        return json_encode($this);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->identifier
        ];
    }

    public static function fromArray(array $scope)
    {
        return new self($scope['id']);
    }
}