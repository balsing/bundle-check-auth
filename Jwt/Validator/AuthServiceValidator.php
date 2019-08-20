<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Validator;

use YouTool\AuthBundle\Jwt\Token\TokenInterface;
use YouTool\AuthBundle\Service\AuthServiceInterface;
use YouTool\AuthBundle\Exception\TransportException;
use YouTool\AuthBundle\Exception\InvalidTokenException;

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
