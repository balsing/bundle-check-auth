<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\TokenFactory;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;

/**
 * Интерфейс для объекта, который создает jwt.
 */
interface TokenFactoryInterface
{
    /**
     * Создает токениз строки.
     */
    public function createTokenFromString(string $stringWithToken): TokenInterface;
}
