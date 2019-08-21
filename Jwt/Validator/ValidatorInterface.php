<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Validator;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;

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
