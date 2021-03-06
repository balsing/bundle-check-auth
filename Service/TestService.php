<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Service;

use Youtool\AuthBundle\Jwt\Token\Token;
use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Youtool\AuthBundle\Exception\TransportException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use InvalidArgumentException;

/**
 * Объект-заглушка, который эмулирует сервис авторизации youtool.
 */
class TestService implements AuthServiceInterface
{
    /**
     * Объект с настройками для доступа к сервису авторизации.
     *
     * @var ConfigInterface
     */
    protected $config;
    /**
     * Uuid пользователя, от имени которого пройдет авторизация.
     *
     * @var string
     */
    protected $sub;

    /**
     * Телефон пользователя от имени которого пройдет авторизация.
     *
     * @var string
     */
    protected $phone;
    /**
     * Пользователи, для которых пройдет валидация.
     *
     * @var string[]
     */
    protected $allowedSubs = [];
    /**
     * Авторизационный код.
     *
     * @var string
     */
    protected $authCode;
    /**
     * Токен для обновления токена доступа.
     *
     * @var string
     */
    protected $refreshToken;

    public function __construct(ConfigInterface $config, string $sub, string $phone, array $allowedSubs = [])
    {
        $this->config = $config;
        $this->sub = $sub;
        $this->phone = $phone;
        $this->allowedSubs = array_unique(array_merge(array_map('trim', $allowedSubs), [$sub]));
        $this->authCode = 'test_auth_code';
        $this->refreshToken = 'test_refresh_token';
    }

    /**
     * @inheritdoc
     */
    public function createAuthorizeUrl(array $scopes = [], string $redirectUri = ''): string
    {
        $url = $redirectUri ?: $this->config->getRedirectUri();
        $url = rtrim($url, ' /\\');

        return $url . '?' . http_build_query([
            'code' => $this->authCode,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getGrantTokensByCode(string $code): TokenResponse
    {
        if ($code !== $this->authCode) {
            throw new TransportException('Wrong auth code');
        }

        return new TokenResponse(
            $this->createFakeToken($this->sub, $this->phone),
            $this->refreshToken
        );
    }

    /**
     * @inheritdoc
     */
    public function getGrantTokensByPasswordCredentials(string $username, string $password, array $scopes = []): TokenResponse
    {
        if ($username !== $this->sub) {
            throw new TransportException('Wrong user name');
        }

        return new TokenResponse(
            $this->createFakeToken($this->sub, $this->phone),
            $this->refreshToken
        );
    }

    /**
     * @inheritdoc
     */
    public function getGrantTokensByRefreshToken(string $refreshToken): TokenResponse
    {
        if ($refreshToken !== $this->refreshToken) {
            throw new TransportException('Wrong refresh code');
        }

        return new TokenResponse(
            $this->createFakeToken($this->sub, $this->phone),
            $this->refreshToken
        );
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        $result = true;

        if (!in_array($token->getUserId(), $this->allowedSubs)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Создает фэйковый токен.
     */
    public function createFakeToken(string $sub, string $phone): TokenInterface
    {
        $time = time();
        $token = (new Builder())
            ->setAudience($this->config->getClientId())
            ->setId(md5(random_bytes(80)), true)
            ->setIssuedAt($time)
            ->setNotBefore($time)
            ->setExpiration($time + 60 * 60)
            ->setSubject($sub)
            ->set('scopes', $this->config->getAuthScopes())
            ->set('phone', $phone)
            ->sign(new Sha256, new Key($this->getPathToPrivateKey()))
            ->getToken();

        return new Token($token);
    }

    /**
     * Возвращает путь к сертификату для подписи токена.
     *
     * @throws InvalidArgumentException
     */
    protected function getPathToPrivateKey(): string
    {
        $pathToPrivateKey = dirname(__DIR__) . '/Resources/keys/test.private.key';

        if (!file_exists($pathToPrivateKey)) {
            throw new InvalidArgumentException(
                "Private key file {$pathToPrivateKey} doesn't exist."
            );
        }

        return 'file://' . $pathToPrivateKey;
    }
}
