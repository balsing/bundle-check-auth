<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Service;

/**
 * Интерфейс для объекта, который содержит настройки для доступа к сервису
 * авторизации youtool.
 */
interface ConfigInterface
{
    /**
     * Возвращает базовую (протокол и домен) ссылку на сервис авторизации.
     */
    public function getBaseUri(): string;

    /**
     * Возвращает идентификатор клиента.
     */
    public function getClientId(): string;

    /**
     * Возвращает пароль клиента.
     */
    public function getClientSecret(): string;

    /**
     * Возвращает ссылку для возврата пользователя после авторизации.
     */
    public function getRedirectUri(): string;

    /**
     * Возвращает списк разрешений, которые нужно запросить при авторизации.
     *
     * @return string[]
     */
    public function getAuthScopes(): array;
}
