<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Youtool\AuthBundle\Jwt\Token\Token as YoutoolToken;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;

/**
 * Класс для тестов, который содержит хэлперы для токенов.
 */
abstract class JwtCase extends BaseCase
{
    /**
     * Создает объект токена для проверки.
     */
    protected function createToken(int $expired = null, array $scopes = [], string $sub = ''): TokenInterface
    {
        $expired = $expired ?: time();
        $sub = $sub ?: '550e8400-e29b-41d4-a716-446655440001';

        $externalToken = (new Builder())
            ->setAudience('550e8400-e29b-41d4-a716-446655440000')
            ->setId($this->createFakeData()->word, true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($expired)
            ->setSubject($sub)
            ->set('scopes', $scopes)
            ->getToken();

        return new YoutoolToken($externalToken);
    }

    /**
     * Создает объект токена из указанного файла с токеном.
     */
    protected function restoreTokenFromFile(string $pathToFile = null): TokenInterface
    {
        $pathToFile = $pathToFile ?: __DIR__ . '/_fixture/token.txt';
        $externalToken = (new Parser)->parse(file_get_contents($pathToFile));

        return new YoutoolToken($externalToken);
    }
}
