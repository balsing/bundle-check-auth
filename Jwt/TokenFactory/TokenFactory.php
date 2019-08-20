<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\TokenFactory;

use YouTool\AuthBundle\Jwt\Token\Token;
use YouTool\AuthBundle\Jwt\Token\TokenInterface;
use YouTool\AuthBundle\Exception\InvalidTokenException;
use Lcobucci\JWT\Parser;
use Exception;

/**
 * Объект, который создает jwt.
 */
class TokenFactory implements TokenFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function createTokenFromString(string $stringWithToken): TokenInterface
    {
        try {
            $parser = new Parser;
            $token = new Token($parser->parse($stringWithToken));
        } catch (Exception $e) {
            throw new InvalidTokenException("Can't create token from string", 0, $e);
        }

        return $token;
    }
}
