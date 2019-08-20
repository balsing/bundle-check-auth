<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\TokenFactory;

use YouTool\AuthBundle\Jwt\Token\TokenInterface;

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
