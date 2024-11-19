<?php

namespace App\DataService;

class ApiTokenQueryService
{
    /** @var string[] */
    private array $apiTokens;

    /**
     * @param string[] $apiTokens
     */
    public function __construct(array $apiTokens)
    {
        $this->apiTokens = $apiTokens;
    }

    public function TokenIsValid(string $token): bool
    {
        return in_array($token, $this->apiTokens, true);
    }
}