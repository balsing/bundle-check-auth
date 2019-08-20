<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Token;

use YouTool\AuthBundle\CryptKey\CryptKeyInterface;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * Объект, в котором хранится jwt.
 */
class Token implements TokenInterface
{
    /**
     * @var \Lcobucci\JWT\Token
     */
    protected $token;

    public function __construct(\Lcobucci\JWT\Token $token)
    {
        $this->token = $token;
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return (string) $this->token;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return (string) $this->token->getClaim('jti');
    }

    /**
     * @inheritdoc
     */
    public function getClientId(): string
    {
        return (string) $this->token->getClaim('aud');
    }

    /**
     * @inheritdoc
     */
    public function getExpired(): int
    {
        return (int) $this->token->getClaim('exp');
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): string
    {
        return (string) $this->token->getClaim('sub');
    }

    /**
     * Возвращает список разрешений из токена.
     *
     * @return string[]
     */
    public function getScopes(): array
    {
        $scopes = $this->token->getClaim('scopes');
        $scopes = is_array($scopes) ? array_map('trim', $scopes) : [];

        return $scopes;
    }

    /**
     * @inheritdoc
     */
    public function isVerifiedByKey(CryptKeyInterface $key): bool
    {
        return $this->token->verify(new Sha256, $key->getKeyPath());
    }

    /**
     * @inheritdoc
     */
    public function getClaim(string $claim): string
    {
        $return = '';

        $claim = trim(strtolower($claim));
        if ($this->token->hasClaim($claim)) {
            $return = (string) $this->token->getClaim($claim);
        }

        return $return;
    }
}
