<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Handler;

use Youtool\AuthBundle\Exception\InvalidTokenException;
use Youtool\AuthBundle\Jwt\Parser\ParserInterface;
use Youtool\AuthBundle\Jwt\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Exception;

/**
 * Объект обработчика токена: сочетает в себе функционал
 * как парсера, так и валидации. Собирается из парсера и нескольких валидаторов.
 */
class CompositeHandler implements HandlerInterface
{
    /**
     * @var ParserInterface
     */
    protected $parser;
    /**
     * @var ValidatorInterface[]
     */
    protected $validators;
    /**
     * @var bool[]
     */
    protected $validationResults = [];

    /**
     * @param ParserInterface      $parser
     * @param ValidatorInterface[] $validators
     */
    public function __construct(ParserInterface $parser, iterable $validators = [])
    {
        $this->parser = $parser;
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function doesHttpRequestHasToken(Request $request): bool
    {
        try {
            $doesHttpRequestHasToken = $this->parser->doesHttpRequestHasToken($request);
        } catch (Exception $e) {
            throw $this->processInternalException($e);
        }

        return $doesHttpRequestHasToken;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function parseTokenFromHttpRequest(Request $request): TokenInterface
    {
        try {
            $token = $this->parser->parseTokenFromHttpRequest($request);
        } catch (Exception $e) {
            throw $this->processInternalException($e);
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        $id = $token->getId();

        if (!isset($this->validationResults[$id])) {
            $this->validationResults[$id] = $this->validateTokenInternal($token);
        }

        return $this->validationResults[$id];
    }

    /**
     * Проверяет токен на валидность с помощью заданных валидаторов.
     *
     * @throws InvalidTokenException
     */
    protected function validateTokenInternal(TokenInterface $token): bool
    {
        $isTokenValid = true;

        try {
            foreach ($this->validators as $validator) {
                if (!$validator->isTokenValid($token)) {
                    $isTokenValid = false;
                    break;
                }
            }
        } catch (Exception $e) {
            throw $this->processInternalException($e);
        }

        return $isTokenValid;
    }

    /**
     * Обратаывает внутренние исключения.
     */
    protected function processInternalException(Exception $e, string $message = null): InvalidTokenException
    {
        $message = $message ?: $e->getMessage();

        return new InvalidTokenException($message, 0, $e);
    }
}
