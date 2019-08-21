<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\Service;

use Youtool\AuthBundle\Tests\JwtCase;
use Youtool\AuthBundle\Service\AuthService;
use Youtool\AuthBundle\Service\ArrayConfig;
use Youtool\AuthBundle\Service\TokenResponse;
use Youtool\AuthBundle\Jwt\TokenFactory\TokenFactoryInterface;
use Youtool\AuthBundle\Jwt\TokenFactory\TokenFactory;
use Youtool\AuthBundle\Exception\TransportException;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;

/**
 * Набор тестов для объекта, который обращается к сервису авторизации.
 */
class AuthServiceTest extends JwtCase
{
    /**
     * Проверяет, что объект верно формирует ссылку на авторизацию.
     */
    public function testCreateAuthorizeUrl()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $defaultRedirectUri = $this->createFakeData()->unique()->url;
        $redirectUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->unique()->uuid;
        $scopes = [
            $this->createFakeData()->unique()->word,
            $this->createFakeData()->unique()->word,
        ];

        $config = new ArrayConfig($baseUri, $clientId, '', $defaultRedirectUri);
        $transport = $this->getMockBuilder(ClientInterface::class)->getMock();
        $tokenFactory = $this->getMockBuilder(TokenFactoryInterface::class)->getMock();

        $authService = new AuthService($config, $transport, $tokenFactory);
        $authorizationUrl = $authService->createAuthorizeUrl($scopes, $redirectUri);

        $this->assertStringStartsWith($baseUri, $authorizationUrl);
        $this->assertContains(urlencode($clientId), $authorizationUrl);
        $this->assertContains(urlencode($redirectUri), $authorizationUrl);
        foreach ($scopes as $scope) {
            $this->assertContains(urlencode($scope), $authorizationUrl);
        }
    }

    /**
     * Проверяет, что объект верно получает токен по коду авторизации.
     */
    public function testGetGrantTokensByCode()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $defaultRedirectUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->unique()->uuid;
        $clientSecret = $this->createFakeData()->unique()->word;
        $code = $this->createFakeData()->unique()->word;

        $goodResponseBody = $this->getMockBuilder(StreamInterface::class)->getMock();
        $goodResponseBody->method('getContents')->will($this->returnValue(
            file_get_contents(__DIR__ . '/../_fixture/auth_code_response.json')
        ));
        $goodResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $goodResponse->method('getStatusCode')->will($this->returnValue(200));
        $goodResponse->method('getBody')->will($this->returnValue($goodResponseBody));

        $badResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $badResponse->method('getStatusCode')->will($this->returnValue(504));

        $transport = $this->getMockBuilder(ClientInterface::class)->getMock();
        $transport->method('request')->will($this->returnCallback(
            function ($method, $url, array $options = []) use ($baseUri, $clientId, $clientSecret, $defaultRedirectUri, $code, $goodResponse, $badResponse) {
                $isRequestCorrect = strtolower($method) == 'post'
                    && strpos($url, $baseUri) === 0
                    && !empty($options['form_params'])
                    && $options['form_params']['client_id'] === $clientId
                    && $options['form_params']['client_secret'] === $clientSecret
                    && $options['form_params']['redirect_uri'] === $defaultRedirectUri
                    && $options['form_params']['code'] === $code
                    && $options['form_params']['grant_type'] === 'authorization_code'
                ;

                return $isRequestCorrect ? $goodResponse : $badResponse;
            }
        ));

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret, $defaultRedirectUri);
        $tokenFactory = new TokenFactory;

        $authService = new AuthService($config, $transport, $tokenFactory);
        $tokenResponse = $authService->getGrantTokensByCode($code);

        $this->assertInstanceOf(TokenResponse::class, $tokenResponse);
        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../_fixture/auth_code_response_access_token.txt')),
            $tokenResponse->getAccessTokenString()
        );
        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../_fixture/auth_code_response_refresh_token.txt')),
            $tokenResponse->getRefreshTokenString()
        );
    }

    /**
     * Проверяет, что объект верно получает токен по логину и паролю.
     */
    public function testGetGrantTokensByPasswordCredentials()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->unique()->uuid;
        $clientSecret = $this->createFakeData()->unique()->word;
        $username = $this->createFakeData()->unique()->word;
        $password = $this->createFakeData()->unique()->word;
        $scope = [
            $this->createFakeData()->unique()->word,
            $this->createFakeData()->unique()->word,
        ];

        $goodResponseBody = $this->getMockBuilder(StreamInterface::class)->getMock();
        $goodResponseBody->method('getContents')->will($this->returnValue(
            file_get_contents(__DIR__ . '/../_fixture/auth_code_response.json')
        ));
        $goodResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $goodResponse->method('getStatusCode')->will($this->returnValue(200));
        $goodResponse->method('getBody')->will($this->returnValue($goodResponseBody));

        $badResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $badResponse->method('getStatusCode')->will($this->returnValue(504));

        $transport = $this->getMockBuilder(ClientInterface::class)->getMock();
        $transport->method('request')->will($this->returnCallback(
            function ($method, $url, array $options = []) use ($baseUri, $clientId, $clientSecret, $username, $password, $scope, $goodResponse, $badResponse) {
                $isRequestCorrect = strtolower($method) == 'post'
                    && strpos($url, $baseUri) === 0
                    && !empty($options['form_params'])
                    && $options['form_params']['client_id'] === $clientId
                    && $options['form_params']['client_secret'] === $clientSecret
                    && $options['form_params']['redirect_uri'] === $defaultRedirectUri
                    && $options['form_params']['username'] === $username
                    && $options['form_params']['password'] === $password
                    && $options['form_params']['grant_type'] === 'password'
                    && $options['form_params']['scope'] === implode(' ', $scope)
                ;

                return $isRequestCorrect ? $goodResponse : $badResponse;
            }
        ));

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret, $defaultRedirectUri, $scope);
        $tokenFactory = new TokenFactory;

        $authService = new AuthService($config, $transport, $tokenFactory);
        $tokenResponse = $authService->getGrantTokensByPasswordCredentials($username, $password);

        $this->assertInstanceOf(TokenResponse::class, $tokenResponse);
        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../_fixture/auth_code_response_access_token.txt')),
            $tokenResponse->getAccessTokenString()
        );
        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../_fixture/auth_code_response_refresh_token.txt')),
            $tokenResponse->getRefreshTokenString()
        );
    }

    /**
     * Проверяет, что объект верно получает токен по логину и паролю.
     */
    public function testGetGrantTokensByRefreshToken()
    {
        $baseUri = $this->createFakeData()->unique()->url;
        $clientId = $this->createFakeData()->unique()->uuid;
        $clientSecret = $this->createFakeData()->unique()->word;
        $refreshToken = $this->createFakeData()->unique()->word;
        $redirectUri = $this->createFakeData()->unique()->url;

        $goodResponseBody = $this->getMockBuilder(StreamInterface::class)->getMock();
        $goodResponseBody->method('getContents')->will($this->returnValue(
            file_get_contents(__DIR__ . '/../_fixture/auth_code_response.json')
        ));
        $goodResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $goodResponse->method('getStatusCode')->will($this->returnValue(200));
        $goodResponse->method('getBody')->will($this->returnValue($goodResponseBody));

        $badResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $badResponse->method('getStatusCode')->will($this->returnValue(504));

        $transport = $this->getMockBuilder(ClientInterface::class)->getMock();
        $transport->method('request')->will($this->returnCallback(
            function ($method, $url, array $options = []) use ($baseUri, $clientId, $clientSecret, $refreshToken, $goodResponse, $badResponse) {
                $isRequestCorrect = strtolower($method) == 'post'
                    && strpos($url, $baseUri) === 0
                    && !empty($options['form_params'])
                    && $options['form_params']['client_id'] === $clientId
                    && $options['form_params']['client_secret'] === $clientSecret
                    && $options['form_params']['refresh_token'] === $refreshToken
                    && $options['form_params']['grant_type'] === 'refresh_token'
                ;

                return $isRequestCorrect ? $goodResponse : $badResponse;
            }
        ));

        $config = new ArrayConfig($baseUri, $clientId, $clientSecret, $defaultRedirectUri, []);
        $tokenFactory = new TokenFactory;

        $authService = new AuthService($config, $transport, $tokenFactory);
        $tokenResponse = $authService->getGrantTokensByRefreshToken($refreshToken);

        $this->assertInstanceOf(TokenResponse::class, $tokenResponse);
        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../_fixture/auth_code_response_access_token.txt')),
            $tokenResponse->getAccessTokenString()
        );
        $this->assertSame(
            trim(file_get_contents(__DIR__ . '/../_fixture/auth_code_response_refresh_token.txt')),
            $tokenResponse->getRefreshTokenString()
        );
    }

    /**
     * Проверяет, что объект корректно проверяет токен на сервисе авторизации.
     */
    public function testIsTokenValid()
    {
        $baseUri = $this->createFakeData()->url;
        $tokenString = file_get_contents(__DIR__ . '/../_fixture/token.txt');

        $goodResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $goodResponse->method('getStatusCode')->will($this->returnValue(204));

        $badResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $badResponse->method('getStatusCode')->will($this->returnValue(504));

        $config = new ArrayConfig($baseUri, '', '', $baseUri);

        $transport = $this->getMockBuilder(ClientInterface::class)->getMock();
        $transport->method('request')->will($this->returnCallback(function ($method, $url, array $options = []) use ($baseUri, $tokenString, $goodResponse, $badResponse) {
            $isRequestCorrect = strtolower($method) == 'get'
                && strpos($url, $baseUri) === 0
                && !empty($options['headers']['Authorization'])
                && $options['headers']['Authorization'] === "Bearer {$tokenString}"
            ;

            return $isRequestCorrect ? $goodResponse : $badResponse;
        }));

        $tokenFactory = $this->getMockBuilder(TokenFactoryInterface::class)->getMock();

        $properToken = $this->restoreTokenFromFile(__DIR__ . '/../_fixture/token.txt');
        $badToken = $this->restoreTokenFromFile(__DIR__ . '/../_fixture/token_bad.txt');

        $authService = new AuthService($config, $transport, $tokenFactory);

        $this->assertTrue($authService->isTokenValid($properToken), 'Valid token');
        $this->assertFalse($authService->isTokenValid($badToken), 'Invalid token');
    }

    /**
     * Проверяет, что объект перехватит исключение от транспорта.
     */
    public function testIsTokenValidClientException()
    {
        $baseUri = $this->createFakeData()->url;
        $config = new ArrayConfig($baseUri, '', '', $baseUri);

        $transport = $this->getMockBuilder(ClientInterface::class)->getMock();
        $transport->method('request')->will($this->throwException(new UnexpectedValueException));

        $properToken = $this->restoreTokenFromFile(__DIR__ . '/../_fixture/token.txt');

        $tokenFactory = $this->getMockBuilder(TokenFactoryInterface::class)->getMock();

        $authService = new AuthService($config, $transport, $tokenFactory);

        $this->expectException(TransportException::class);
        $authService->isTokenValid($properToken);
    }
}
