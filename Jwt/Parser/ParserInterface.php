<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Parser;

use Symfony\Component\HttpFoundation\Request;
use YouTool\AuthBundle\Jwt\Token\TokenInterface;

/**
 * Интерфейс для объекта, который получает токен из объекта http запроса symfony.
 */
interface ParserInterface
{
    /**
     * Проверяет содержится ли токен в объекте http запроса symfony.
     */
    public function doesHttpRequestHasToken(Request $request): bool;

    /**
     * Получение токена из объекта http запроса symfony.
     */
    public function parseTokenFromHttpRequest(Request $request): TokenInterface;
}
