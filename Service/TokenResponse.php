<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Service;

use YouTool\AuthBundle\Jwt\Token\TokenInterface;

/**
 * Объект, который содержит успешный ответ от сервиса авторизации с токенами.
 */
class TokenResponse
{
    /**
     * Токен доступа.
     *
     * @var string
     */
    protected $accessToken = '';
    /**
     * Токен для обновления токена доступа.
     *
     * @var string
     */
    protected $refreshToken = '';

    public function __construct(TokenInterface $accessToken, string $refreshToken)
    {
        $this->accessToken = $accessToken->toString();
        $this->refreshToken = $refreshToken;
    }

    /**
     * Возвращает токен доступа.
     */
    public function getAccessTokenString(): string
    {
        return $this->accessToken;
    }

    /**
     * Возвращает токен обновления.
     */
    public function getRefreshTokenString(): string
    {
        return $this->refreshToken;
    }
}
