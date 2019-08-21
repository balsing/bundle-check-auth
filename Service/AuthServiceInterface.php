<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Service;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Youtool\AuthBundle\Exception\TransportException;
use Youtool\AuthBundle\Exception\InvalidTokenException;

/**
 * Интерфейс для объекта, который обращется к сервису авторизации youtool.
 */
interface AuthServiceInterface
{
    /**
     * Создает ссылку на авторизацию на сервисе авторизации.
     *
     * @param string[] $scopes
     * @param string   $redirectUri
     */
    public function createAuthorizeUrl(array $scopes = [], string $redirectUri = ''): string;

    /**
     * Получает токен доступа и токен для обновления по коду авторизации.
     *
     * @throws TransportException
     * @throws InvalidTokenException
     */
    public function getGrantTokensByCode(string $code): TokenResponse;

    /**
     * Получает токен доступа и токен для обновления с помощью передачи логина
     * и пароля пользователя.
     *
     * @throws TransportException
     * @throws InvalidTokenException
     */
    public function getGrantTokensByPasswordCredentials(string $username, string $password, array $scopes = []): TokenResponse;

    /**
     * Получает новые токен доступа и токен для обновления с помощью токена обновления.
     *
     * @throws TransportException
     * @throws InvalidTokenException
     */
    public function getGrantTokensByRefreshToken(string $refreshToken): TokenResponse;

    /**
     * Проверяет валидность токена на сервисе авторизации.
     *
     * @throws TransportException
     * @throws InvalidTokenException
     */
    public function isTokenValid(TokenInterface $token): bool;
}
