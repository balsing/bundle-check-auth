<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Parser;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Youtool\AuthBundle\Jwt\TokenFactory\TokenFactoryInterface;
use Youtool\AuthBundle\Jwt\TokenFactory\TokenFactory;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;
use Exception;

/**
 * Объект, который получает jwt из объекта http запроса symfony.
 */
class BearerParser implements ParserInterface
{
    /**
     * @var TokenFactoryInterface
     */
    protected $tokenFactory;

    public function __construct(TokenFactoryInterface $tokenFactory = null)
    {
        $this->tokenFactory = $tokenFactory ?: new TokenFactory;
    }

    /**
     * @inheritdoc
     */
    public function doesHttpRequestHasToken(Request $request): bool
    {
        return $this->extractTokenStringFromHttpRequest($request) !== '';
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException
     */
    public function parseTokenFromHttpRequest(Request $request): TokenInterface
    {
        $tokenString = $this->extractTokenStringFromHttpRequest($request);

        try {
            $token = $this->tokenFactory->createTokenFromString($tokenString);
        } catch (Exception $e) {
            throw new UnexpectedValueException("Can't parse token from request.", 0, $e);
        }

        return $token;
    }

    /**
     * Извлекает строку с токеном из запроса.
     */
    protected function extractTokenStringFromHttpRequest(Request $request): string
    {
        $authHeader = $request->headers->get('Authorization');
        $authHeaderValue = is_array($authHeader) ? end($authHeader) : $authHeader;

        return $this->extractTokenStringFromBearerValue((string) $authHeaderValue);
    }

    /**
     * Извлекает токен из строки Bearer заголовка.
     */
    protected function extractTokenStringFromBearerValue(string $bearerValue): string
    {
        $return = '';

        if (preg_match('/^(?:\s+)?Bearer\s(.+)$/', $bearerValue, $matches) && !empty($matches[1])) {
            $return = trim((string) $matches[1]);
        }

        return $return;
    }
}
