<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Youtool\AuthBundle\Jwt\Handler\HandlerInterface;
use Youtool\AuthBundle\Exception\InvalidTokenException;
use Youtool\AuthBundle\Jwt\Token\TokenInterface as JwtTokenInterface;

/**
 * Guard для авторизации через токен сервиса авторизации youtool.
 */
class JwtAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var HandlerInterface
     */
    protected $jwtHandler;

    public function __construct(HandlerInterface $jwtHandler)
    {
        $this->jwtHandler = $jwtHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function supports(Request $request)
    {
        return $this->jwtHandler->doesHttpRequestHasToken($request);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function getCredentials(Request $request)
    {
        return $this->jwtHandler->parseTokenFromHttpRequest($request);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = null;
        $token = $this->extractTokenFromCredentials($credentials);

        if (($userProvider instanceof JwtUserProviderInterface) && $this->jwtHandler->isTokenValid($token)) {
            $user = $userProvider->loadUserByToken($token);
        } else {
            $user = $userProvider->loadUserByUsername($token->getUserId());
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $token = $this->extractTokenFromCredentials($credentials);

        return $this->jwtHandler->isTokenValid($token);
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $jwt = $this->jwtHandler->parseTokenFromHttpRequest($request);
        $token->setAttribute('jwt', $jwt);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return null;
    }

    /**
     * @inheritdoc
     *
     * @throws AccessDeniedHttpException
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new AccessDeniedHttpException('Access denied.', $authException);
    }

    /**
     * @inheritdoc
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Получает токен из авторизационных данных пользователя.
     *
     * @param mixed $credentials
     *
     * @throws InvalidTokenException
     */
    protected function extractTokenFromCredentials($credentials): JwtTokenInterface
    {
        if (!($credentials instanceof JwtTokenInterface)) {
            throw new InvalidTokenException('There is no token in credentials.');
        }

        return $credentials;
    }
}
