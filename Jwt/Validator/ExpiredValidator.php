<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Validator;

use Youtool\AuthBundle\Jwt\Token\TokenInterface;
use InvalidArgumentException;

/**
 * Объект, который проверяет, что jwt еще не истек.
 */
class ExpiredValidator implements ValidatorInterface
{
    /**
     * Количество секунд, которые должны оставаться до того, как токен будет
     * просрочен. 0 - не учитывать таймаут.
     *
     * Используется для того, чтобы учесть лаг на сетевой запрос к сервису
     * авторизации.
     *
     * @var int
     */
    protected $expiredTimeout = 0;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $expiredTimeout = 0)
    {
        if ($expiredTimeout < 0) {
            throw new InvalidArgumentException(
                "Expired timeout can't be less than 0."
            );
        }

        $this->expiredTimeout = $expiredTimeout;
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid(TokenInterface $token): bool
    {
        $expired = $token->getExpired();
        $timeToTest = time() + $this->expiredTimeout;

        return $expired >= $timeToTest;
    }
}
