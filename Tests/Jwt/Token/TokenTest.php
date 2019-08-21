<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\Jwt;

use Youtool\AuthBundle\Tests\JwtCase;

/**
 * Набор тестов для токена.
 */
class TokenTest extends JwtCase
{
    /**
     * Проверяет, что токен верно приводится к строке.
     */
    public function testToString()
    {
        $tokenString = file_get_contents(__DIR__ . '/../../_fixture/token.txt');
        $token = $this->restoreTokenFromFile();

        $this->assertSame($tokenString, $token->toString());
    }

    /**
     * Проверяет, что токен верно извлекает идентификатор из jwt.
     */
    public function testGetId()
    {
        $token = $this->restoreTokenFromFile();

        $this->assertSame('304e5ff9844c4be464ad868913934ff0872a625ffef424eafc1aa62ba860f23816f7032bce1e2da6', $token->getId());
    }

    /**
     * Проверяет, что токен верно извлекает идентификатор пользователя из jwt.
     */
    public function testGetUserId()
    {
        $token = $this->restoreTokenFromFile();

        $this->assertSame('d069366c-a669-4379-b9b5-0f0f18d6b0c5', $token->getUserId());
    }

    /**
     * Проверяет, что токен верно извлекает идентификатор клиента из jwt.
     */
    public function testGetClientId()
    {
        $token = $this->restoreTokenFromFile();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $token->getClientId());
    }

    /**
     * Проверяет, что токен верно извлекает время истечения токена из jwt.
     */
    public function testGetExpired()
    {
        $token = $this->restoreTokenFromFile();

        $this->assertSame(1550476644, $token->getExpired());
    }

    /**
     * Проверяет, что токен верно извлекает список разрешений из jwt.
     */
    public function testGetScopes()
    {
        $token = $this->restoreTokenFromFile();

        $this->assertSame(['allowed'], $token->getScopes());
    }

    /**
     * Проверяет, что токен возвращает именованые кастомные данные из jwt.
     */
    public function testGetClaim()
    {
        $token = $this->restoreTokenFromFile();

        $this->assertSame('test@test.test', $token->getClaim('email'));
    }
}
