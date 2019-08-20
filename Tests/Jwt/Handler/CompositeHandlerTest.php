<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Tests\Jwt\Handler;

use YouTool\AuthBundle\Tests\JwtCase;
use YouTool\AuthBundle\Jwt\Handler\CompositeHandler;
use YouTool\AuthBundle\Jwt\Parser\ParserInterface;
use YouTool\AuthBundle\Jwt\Validator\ValidatorInterface;
use YouTool\AuthBundle\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

/**
 * Набор тестов для составного обработчика токена.
 */
class CompositeHandlerTest extends JwtCase
{
    /**
     * Проверяет, что объект верно указывает наличие токена в запросе.
     */
    public function testDoesHttpRequestHasToken()
    {
        $goodRequest = $this->getMockBuilder(Request::class)->getMock();
        $badRequest = $this->getMockBuilder(Request::class)->getMock();

        $parser = $this->getMockBuilder(ParserInterface::class)->getMock();
        $parser->method('doesHttpRequestHasToken')->will($this->returnCallback(function ($request) use ($goodRequest) {
            return $request === $goodRequest;
        }));

        $compositeHandler = new CompositeHandler($parser);

        $this->assertTrue(
            $compositeHandler->doesHttpRequestHasToken($goodRequest),
            'Request has token'
        );
        $this->assertFalse(
            $compositeHandler->doesHttpRequestHasToken($badRequest),
            'Request does not have token'
        );
    }

    /**
     * Проверяет, что объект перехватывает исключение при проверке наличия токена.
     */
    public function testDoesHttpRequestHasTokenException()
    {
        $request = $this->getMockBuilder(Request::class)->getMock();

        $parser = $this->getMockBuilder(ParserInterface::class)->getMock();
        $parser->method('doesHttpRequestHasToken')->will($this->throwException(new UnexpectedValueException));

        $compositeHandler = new CompositeHandler($parser);

        $this->expectException(InvalidTokenException::class);
        $compositeHandler->doesHttpRequestHasToken($request);
    }

    /**
     * Проверяет, что объект верно извлекает токен из запроса.
     */
    public function testParseTokenFromHttpRequest()
    {
        $token = $this->createToken();
        $goodRequest = $this->getMockBuilder(Request::class)->getMock();

        $parser = $this->getMockBuilder(ParserInterface::class)->getMock();
        $parser->method('parseTokenFromHttpRequest')->will($this->returnCallback(function ($request) use ($goodRequest, $token) {
            return $request === $goodRequest ? $token : null;
        }));

        $compositeHandler = new CompositeHandler($parser);
        $extractedToken = $compositeHandler->parseTokenFromHttpRequest($goodRequest);

        $this->assertSame($token, $extractedToken);
    }

    /**
     * Проверяет, что объект перехватывает исключение при получении токена из запроса.
     */
    public function testParseTokenFromHttpRequestException()
    {
        $request = $this->getMockBuilder(Request::class)->getMock();

        $parser = $this->getMockBuilder(ParserInterface::class)->getMock();
        $parser->method('parseTokenFromHttpRequest')->will($this->throwException(new UnexpectedValueException));

        $compositeHandler = new CompositeHandler($parser);

        $this->expectException(InvalidTokenException::class);
        $compositeHandler->parseTokenFromHttpRequest($request);
    }

    /**
     * Проверяет, что объект валидирует токен.
     */
    public function testIsTokenValid()
    {
        $token = $this->createToken();
        $request = $this->getMockBuilder(Request::class)->getMock();
        $parser = $this->getMockBuilder(ParserInterface::class)->getMock();

        $validator1 = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $validator1->expects($this->once())
            ->method('isTokenValid')
            ->will($this->returnCallback(function ($tokenToValidate) use ($token) {
                return $tokenToValidate === $token;
            }));

        $validator2 = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $validator2->expects($this->once())->method('isTokenValid')->will($this->returnValue(false));

        $validator3 = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $validator3->expects($this->never())->method('isTokenValid');

        $compositeHandler = new CompositeHandler($parser, [
            $validator1,
            $validator2,
            $validator3,
        ]);

        $compositeHandler->isTokenValid($token);
        $this->assertFalse($compositeHandler->isTokenValid($token));
    }

    /**
     * Проверяет, что объект обрабатываетисключения при валидации токена.
     */
    public function testIsTokenValidException()
    {
        $token = $this->createToken();
        $request = $this->getMockBuilder(Request::class)->getMock();
        $parser = $this->getMockBuilder(ParserInterface::class)->getMock();

        $validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $validator->method('isTokenValid')->will($this->throwException(new UnexpectedValueException));

        $compositeHandler = new CompositeHandler($parser, [$validator]);

        $this->expectException(InvalidTokenException::class);
        $compositeHandler->isTokenValid($token);
    }
}
