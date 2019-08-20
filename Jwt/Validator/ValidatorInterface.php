<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Validator;

use YouTool\AuthBundle\Jwt\Token\TokenInterface;

/**
 * Интерфейс для объекта, который проверяет jwt на валидность.
 */
interface ValidatorInterface
{
    /**
     * Проверяет содержится ли токен в объекте http запроса symfony.
     */
    public function isTokenValid(TokenInterface $token): bool;
}
