<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Validator;

use YouTool\AuthBundle\Jwt\Token\TokenInterface;
use InvalidArgumentException;

/**
 * Объект, который проверяет, что jwt содержит все требуемые разрешения.
 */
class RequiredScopesValidator implements ValidatorInterface
{
    /**
     * Список обязательных разрешений, которые должен иметь токен.
     *
     * @var string[]
     */
    protected $requiredScopes = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $requiredScopes)
    {
        $this->requiredScopes = $this->clearScopes($requiredScopes);
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        $scopes = $this->clearScopes($token->getScopes());
        $diff = array_diff($this->requiredScopes, $scopes);

        return empty($diff);
    }

    /**
     * Приводит массив разрешений к унифицированному виду.
     *
     * @param mixed[] $scopes
     *
     * @return string[]
     */
    public function clearScopes(array $scopes): array
    {
        $clearedScopes = [];

        foreach ($scopes as $scope) {
            $scope = strtolower((string) $scope);
            if ($scope !== '') {
                $clearedScopes[] = $scope;
            }
        }

        return $clearedScopes;
    }
}
