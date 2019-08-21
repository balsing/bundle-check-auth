<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Validator;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Youtool\AuthBundle\Service\AuthServiceInterface;
use Youtool\AuthBundle\Exception\TransportException;
use Youtool\AuthBundle\Exception\InvalidTokenException;

/**
 * Объект, который проверяет jwt с помощью запроса к сервису авторизации.
 */
class AuthServiceValidator implements ValidatorInterface
{
    /**
     * @var AuthServiceInterface
     */
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     * @throws TransportException
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        return $this->authService->isTokenValid($token);
    }
}
