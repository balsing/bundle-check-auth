<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Token;

use YouTool\AuthBundle\CryptKey\CryptKeyInterface;

/**
 * Интерфейс для объекта, в котором хранится jwt.
 */
interface TokenInterface
{
    /**
     * Конвертирует токен в строку.
     */
    public function toString(): string;

    /**
     * Возвращает идентификатор токена.
     */
    public function getId(): string;

    /**
     * Возвращает идентификатор клиента, для которого был получен токен.
     */
    public function getClientId(): string;

    /**
     * Проверяет истечет ли токен к указанному времени.
     */
    public function getExpired(): int;

    /**
     * Возвращает идентификатор пользователя из токена.
     */
    public function getUserId(): string;

    /**
     * Возвращает список разрешений из токена.
     *
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * Проверяет подпись токена с помощью ключа.
     */
    public function isVerifiedByKey(CryptKeyInterface $key): bool;

    /**
     * Возвращает именованый кастомный параметр из токена.
     */
    public function getClaim(string $claim): string;
}
