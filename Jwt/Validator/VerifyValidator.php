<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Validator;

use Youtool\AuthBundle\CryptKey\CryptKeyInterface;
use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use InvalidArgumentException;

/**
 * Объект, который проверяет, что jwt подписан тем закрытым ключом, для которого
 * указан открытый ключ.
 */
class VerifyValidator implements ValidatorInterface
{
    /**
     * @var CryptKeyInterface
     */
    protected $key;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(CryptKeyInterface $key)
    {
        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        return $token->isVerifiedByKey($this->key);
    }
}
