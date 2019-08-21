<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Service;

use InvalidArgumentException;

/**
 * Объект, который содержит в памяти настройки для доступа к сервису
 * авторизации youtool.
 */
class ArrayConfig implements ConfigInterface
{
    /**
     * @var string
     */
    protected $baseUri;
    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;
    /**
     * @var string
     */
    protected $redirectUri;
    /**
     * @var string[]
     */
    protected $authScopes;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $baseUri, string $clientId, string $clientSecret, string $redirectUri = null, array $authScopes = [])
    {
        if (!$this->isStringAbsoluteUri($baseUri)) {
            throw new InvalidArgumentException(
                'baseUri parameter must be an absolute uri'
            );
        }

        if ($redirectUri !== null && !$this->isStringAbsoluteUri($redirectUri)) {
            throw new InvalidArgumentException(
                'redirectUri parameter must be an absolute uri'
            );
        }

        $this->baseUri = rtrim($baseUri, ' /');
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri ?: '';
        $this->authScopes = array_diff(array_map('trim', $authScopes), ['']);
    }

    /**
     * @inheritdoc
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @inheritdoc
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @inheritdoc
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @inheritdoc
     */
    public function getAuthScopes(): array
    {
        return $this->authScopes;
    }

    /**
     * Проверяет, что строка содержит абсолютную ссылку.
     */
    protected function isStringAbsoluteUri(string $uri): bool
    {
        return (bool) preg_match('/^https?:\/\/\S+$/', $uri);
    }
}
