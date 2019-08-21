<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\CryptKey;

use Youtool\AuthBundle\Tests\JwtCase;
use Youtool\AuthBundle\Security\JwtAuthenticator;
use Youtool\AuthBundle\Jwt\Handler\HandlerInterface;
use Youtool\AuthBundle\Exception\InvalidTokenException;
use Youtool\AuthBundle\Security\JwtUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as SecurityToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Набор тестов для объекта guard'а, который авторизует пользователя по jwt.
 */
class JwtAuthenticatorTest extends JwtCase
{
    /**
     * Проверяет, что объект правильно определяет запросы, которые к нему относятся.
     */
    public function testSupports()
    {
        $request = $this->getMockBuilder(Request::class)->getMock();
        $isSupported = $this->createFakeData()->boolean;

        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $tokenHandler->expects($this->once())
            ->method('doesHttpRequestHasToken')
            ->with($this->equalTo($request))
            ->will($this->returnValue($isSupported));

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertSame($isSupported, $authenticator->supports($request));
    }

    /**
     * Проверяет, что объект правильно извлекает токен из запроса.
     */
    public function testGetCredentials()
    {
        $request = $this->getMockBuilder(Request::class)->getMock();
        $token = $this->createToken();

        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $tokenHandler->expects($this->once())
            ->method('parseTokenFromHttpRequest')
            ->with($this->equalTo($request))
            ->will($this->returnValue($token));

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertSame($token, $authenticator->getCredentials($request));
    }

    /**
     * Проверяет, что объект правильно ищет пользователя по идентификатору.
     */
    public function testGetUserUserProviderInterface()
    {
        $sub = $this->createFakeData()->unique()->uuid;
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $invalidToken = $this->createToken(null, [], $this->createFakeData()->unique()->uuid);
        $token = $this->createToken(null, [], $sub);

        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $tokenHandler->expects($this->never())->method('isTokenValid');

        $userProvider = $this->getMockBuilder(UserProviderInterface::class)->getMock();
        $userProvider->method('loadUserByUsername')->will($this->returnCallback(function ($username) use ($sub, $user) {
            return $username === $sub ? $user : null;
        }));

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertSame($user, $authenticator->getUser($token, $userProvider));
        $this->assertSame(null, $authenticator->getUser($invalidToken, $userProvider));
    }

    /**
     * Проверяет, что объект правильно ищет пользователя по токену.
     */
    public function testGetUserJwtUserProviderInterface()
    {
        $sub = $this->createFakeData()->unique()->uuid;
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $invalidToken = $this->createToken(null, [], $this->createFakeData()->unique()->uuid);
        $token = $this->createToken(null, [], $sub);

        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $tokenHandler->method('isTokenValid')
            ->will($this->returnCallback(function ($tokenToTest) use ($token) {
                return $tokenToTest === $token;
            }));

        $userProvider = $this->getMockBuilder(JwtUserProviderInterface::class)->getMock();
        $userProvider->method('loadUserByToken')->will($this->returnCallback(function ($tokenToTest) use ($token, $user) {
            return $tokenToTest === $token ? $user : null;
        }));

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertSame($user, $authenticator->getUser($token, $userProvider));
        $this->assertSame(null, $authenticator->getUser($invalidToken, $userProvider));
    }

    /**
     * Проверяет, что объект выбросит исключение, если токен не будет указан
     * при поиске пользователя.
     */
    public function testGetUserWrongCredentials()
    {
        $userProvider = $this->getMockBuilder(UserProviderInterface::class)->getMock();
        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->expectException(InvalidTokenException::class);
        $authenticator->getUser([], $userProvider);
    }

    /**
     * Проверяет, что объект проверяет валидность токена.
     */
    public function testCheckCredentials()
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $token = $this->createToken();

        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $tokenHandler->method('isTokenValid')->will($this->returnCallback(function ($tokenToValidate) use ($token) {
            return $tokenToValidate === $token;
        }));

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertTrue($authenticator->checkCredentials($token, $user));
    }

    /**
     * Проверяет, что объект выбросит исключение, если токен не будет указан
     * при проверке валидности токена.
     */
    public function testCheckCredentialsWrongCredentials()
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->expectException(InvalidTokenException::class);
        $authenticator->checkCredentials([], $user);
    }

    /**
     * Проверяет, что guard ничего не делает в случае успеха авторизации.
     */
    public function testOnAuthenticationSuccess()
    {
        $token = $this->createToken();
        $request = $this->getMockBuilder(Request::class)->getMock();

        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $tokenHandler->method('parseTokenFromHttpRequest')
            ->with($this->equalTo($request))
            ->will($this->returnValue($token));

        $securityToken = $this->getMockBuilder(SecurityToken::class)->getMock();
        $securityToken->expects($this->once())
            ->method('setAttribute')
            ->with($this->equalTo('jwt'), $this->equalTo($token));

        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertSame(null, $authenticator->onAuthenticationSuccess($request, $securityToken, ''));
    }

    /**
     * Проверяет, что guard ничего не делает в случае провала авторизации.
     */
    public function testOnAuthenticationFailure()
    {
        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $request = $this->getMockBuilder(Request::class)->getMock();
        $exception = $this->getMockBuilder(AuthenticationException::class)->getMock();
        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertSame(null, $authenticator->onAuthenticationFailure($request, $exception));
    }

    /**
     * Проверяет, что метод выбрасывает исключение AccessDeniedHttpException.
     */
    public function testStart()
    {
        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $request = $this->getMockBuilder(Request::class)->getMock();
        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->expectException(AccessDeniedHttpException::class);
        $authenticator->start($request);
    }

    /**
     * Проверяет, что guard не поддерживает запоминания.
     */
    public function testSupportsRememberMe()
    {
        $tokenHandler = $this->getMockBuilder(HandlerInterface::class)->getMock();
        $authenticator = new JwtAuthenticator($tokenHandler);

        $this->assertFalse($authenticator->supportsRememberMe());
    }
}
