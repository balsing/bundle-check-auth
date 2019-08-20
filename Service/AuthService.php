<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Service;

use YouTool\AuthBundle\Jwt\TokenFactory\TokenFactoryInterface;
use YouTool\AuthBundle\Jwt\Token\TokenInterface;
use YouTool\AuthBundle\Exception\TransportException;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;

/**
 * Объект для обращения к сервису авторизации youtool.
 */
class AuthService implements AuthServiceInterface
{
    /**
     * Объект с настройками для доступа к сервису авторизации.
     *
     * @var ConfigInterface
     */
    protected $config;
    /**
     * Объект, который совершает http запросы.
     *
     * @var ClientInterface
     */
    protected $transport;
    /**
     * Объект, который создает новые токены.
     *
     * @var TokenFactoryInterface
     */
    protected $tokenFactory;

    public function __construct(ConfigInterface $config, ClientInterface $transport, TokenFactoryInterface $tokenFactory)
    {
        $this->config = $config;
        $this->transport = $transport;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @inheritdoc
     */
    public function createAuthorizeUrl(array $scopes = [], string $redirectUri = ''): string
    {
        return $this->createCommandUrl('authorize') . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->config->getClientId(),
            'redirect_uri' => $redirectUri ?: $this->config->getRedirectUri(),
            'scope' => implode(' ', $scopes ?: $this->config->getAuthScopes()),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getGrantTokensByCode(string $code): TokenResponse
    {
        $tokens = $this->requestJson('access_token', 'POST', [
            'form_params' => [
                'client_id' => $this->config->getClientId(),
                'client_secret' => $this->config->getClientSecret(),
                'redirect_uri' => $this->config->getRedirectUri(),
                'code' => $code,
                'grant_type' => 'authorization_code',
            ],
        ]);

        return new TokenResponse(
            $this->tokenFactory->createTokenFromString($tokens['access_token']),
            $tokens['refresh_token']
        );
    }

    /**
     * @inheritdoc
     */
    public function getGrantTokensByPasswordCredentials(string $username, string $password, array $scopes = []): TokenResponse
    {
        $tokens = $this->requestJson('access_token', 'POST', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->config->getClientId(),
                'client_secret' => $this->config->getClientSecret(),
                'username' => $username,
                'password' => $password,
                'scope' => implode(' ', $scopes ?: $this->config->getAuthScopes()),
            ],
        ]);

        return new TokenResponse(
            $this->tokenFactory->createTokenFromString($tokens['access_token']),
            $tokens['refresh_token']
        );
    }

    /**
     * @inheritdoc
     */
    public function getGrantTokensByRefreshToken(string $refreshToken): TokenResponse
    {
        $tokens = $this->requestJson('access_token', 'POST', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->config->getClientId(),
                'client_secret' => $this->config->getClientSecret(),
                'refresh_token' => $refreshToken,
            ],
        ]);

        return new TokenResponse(
            $this->tokenFactory->createTokenFromString($tokens['access_token']),
            $tokens['refresh_token']
        );
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        $requestResult = $this->request('check_access_token', 'GET', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token->toString(),
            ],
        ]);

        return $requestResult->getStatusCode() === 204;
    }

    /**
     * Возвращает обработанный ответ от сервера.
     *
     * @throws TransportException
     */
    protected function requestJson(string $command, string $method = 'GET', array $options = []): array
    {
        $requestResult = $this->request($command, $method, $options);

        if ($requestResult->getStatusCode() !== 200) {
            throw new TransportException(
                'Get ' . $requestResult->getStatusCode() . ' status code while requesting ' . $command . ' command.'
            );
        }

        $body = $requestResult->getBody()->getContents();
        if ($body === '') {
            throw new TransportException(
                'Get empty response while requesting ' . $command . ' command.'
            );
        }

        $decodedResponse = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransportException(
                'Get json parsing error while parsing response of ' . $command . ' command.'
            );
        }

        return $decodedResponse;
    }

    /**
     * Отправляет запрос на сервис.
     *
     * @throws TransportException
     */
    protected function request(string $command, string $method = 'GET', array $options = []): ResponseInterface
    {
        try {
            $url = $this->createCommandUrl($command);
            $requestResult = $this->transport->request($method, $url, $options);
        } catch (Exception $e) {
            throw new TransportException("Can't send request to auth service.", 0, $e);
        }

        return $requestResult;
    }

    /**
     * Создает ссылку на эндпоинт.
     */
    protected function createCommandUrl(string $command): string
    {
        $baseUri = $this->config->getBaseUri();

        return "{$baseUri}/{$command}";
    }
}
