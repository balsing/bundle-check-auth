<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Tests\Service;

use YouTool\AuthBundle\Tests\BaseCase;
use YouTool\AuthBundle\Service\ArrayConfig;
use InvalidArgumentException;

/**
 * Набор тестов для объекта с настройками для доступа к сервису авторизации.
 */
class ArrayConfigTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение, если задать неверную ссылку
     * на сервис.
     */
    public function testConstructWrongBaseUri()
    {
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;
        $redirectUri = $this->createFakeData()->unique()->url;

        $this->expectException(InvalidArgumentException::class);
        new ArrayConfig('/test', $clientId, $clientSecret, $redirectUri);
    }

    /**
     * Проверяет, что объект выбросит исключение, если задать неверную ссылку
     * на сервис.
     */
    public function testConstructWrongRedirectUri()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;
        $redirectUri = $this->createFakeData()->unique()->url;

        $this->expectException(InvalidArgumentException::class);
        new ArrayConfig($baseUri, $clientId, $clientSecret, '/test');
    }

    /**
     * Проверяет, что объект правильно возвращает базовую ссылку на сервис.
     */
    public function testGetBaseUri()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;

        $config = new ArrayConfig($baseUri . '/', $clientId, $clientSecret);

        $this->assertSame(rtrim($baseUri, '/'), $config->getBaseUri());
    }

    /**
     * Проверяет, что объект правильно возвращает идентификатор клиента.
     */
    public function testGetClientId()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret);

        $this->assertSame($clientId, $config->getClientId());
    }

    /**
     * Проверяет, что объект правильно возвращает пароль клиента.
     */
    public function testGetClientSecret()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret);

        $this->assertSame($clientSecret, $config->getClientSecret());
    }

    /**
     * Проверяет, что объект правильно возвращает
     * ссылку на возврат пользователя после авторизации.
     */
    public function testGetRedirectUri()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;
        $redirectUri = $this->createFakeData()->unique()->url;

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret, $redirectUri);

        $this->assertSame($redirectUri, $config->getRedirectUri());
    }

    /**
     * Проверяет, что объект правильно возвращает список разрешений,
     * которые нужно запросить для авторизации.
     */
    public function testGetAuthScopes()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->uuid;
        $clientSecret = $this->createFakeData()->word;
        $redirectUri = $this->createFakeData()->unique()->url;
        $scopes = [
            $this->createFakeData()->word,
            $this->createFakeData()->word,
        ];

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret, $redirectUri, $scopes);

        $this->assertSame($scopes, $config->getAuthScopes());
    }
}
