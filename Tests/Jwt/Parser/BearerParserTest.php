<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Tests\Jwt;

use YouTool\AuthBundle\Jwt\Token\TokenInterface;
use YouTool\AuthBundle\Tests\JwtCase;
use YouTool\AuthBundle\Jwt\Parser\BearerParser;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

/**
 * Набор тестов для объекта, который получает jwt из объекта http запроса symfony.
 */
class BearerParserTest extends JwtCase
{
    /**
     * Проверяет, что парсер определяет наличие bearer заголовка в запросе.
     */
    public function testDoesHttpRequestHasToken()
    {
        $parser = new BearerParser;

        $requestWithToken = $this->createRequestObject([
            'HTTP_AUTHORIZATION' => 'Bearer ' . file_get_contents(__DIR__ . '/../../_fixture/token.txt'),
        ]);
        $requestWithBrokenToken = $this->createRequestObject([
            'HTTP_AUTHORIZATION' => file_get_contents(__DIR__ . '/../../_fixture/token.txt'),
        ]);
        $requestWithoutToken = $this->createRequestObject();

        $this->assertTrue($parser->doesHttpRequestHasToken($requestWithToken), 'Request has token');
        $this->assertFalse($parser->doesHttpRequestHasToken($requestWithBrokenToken), 'Request has broken token');
        $this->assertFalse($parser->doesHttpRequestHasToken($requestWithoutToken), 'Request does not have token');
    }

    /**
     * Проверяет, что парсер извлекает jwt из запроса.
     */
    public function testParseTokenFromHttpRequest()
    {
        $parser = new BearerParser;

        $request = $this->createRequestObject([
            'HTTP_AUTHORIZATION' => 'Bearer ' . file_get_contents(__DIR__ . '/../../_fixture/token.txt'),
        ]);

        $token = $parser->parseTokenFromHttpRequest($request);

        $this->assertInstanceOf(TokenInterface::class, $token);
        $this->assertSame('304e5ff9844c4be464ad868913934ff0872a625ffef424eafc1aa62ba860f23816f7032bce1e2da6', $token->getId());
    }

    /**
     * Проверяет, что парсер выбросит исключение при попытке извлечь токен,
     * если токена нет в запросе.
     */
    public function testParseTokenFromHttpRequestNoTokenException()
    {
        $parser = new BearerParser;
        $request = $this->createRequestObject();

        $this->expectException(UnexpectedValueException::class);
        $parser->parseTokenFromHttpRequest($request);
    }

    /**
     * Создает объект http запроса.
     */
    protected function createRequestObject(array $headers = []): Request
    {
        return new Request([], [], [], [], [], $headers, '');
    }
}
