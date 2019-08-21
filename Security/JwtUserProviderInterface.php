<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Интерфейс для провадера пользователей, который может использовать токен
 * напрямую.
 */
interface JwtUserProviderInterface extends UserProviderInterface
{
    /**
     * Возвращает пользователя, который был найден по данным, полученным из
     * токена.
     *
     * Предполагается, что токен проверен и является валидным.
     *
     * @throws UsernameNotFoundException
     */
    public function loadUserByToken(TokenInterface $token): UserInterface;
}
