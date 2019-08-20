<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Tests\Jwt\Validator;

use YouTool\AuthBundle\Tests\JwtCase;
use YouTool\AuthBundle\Jwt\Validator\ExpiredValidator;
use InvalidArgumentException;

/**
 * Набор тестов для объекта, который проверяет, что jwt еще не истек.
 */
class ExpiredValidatorTest extends JwtCase
{
    /**
     * Проверяет, что объект выбросит исключение
     * при попытке задать таймаут меньше ноля.
     */
    public function testNegativeExpiredTimeoutException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ExpiredValidator(-10);
    }

    /**
     * Проверяет, что валидатор корректно проверяет время истечения токена.
     */
    public function testIsTokenValid()
    {
        $freshToken = $this->createToken(time() + 10);
        $expiredToken = $this->createToken(time() - 10);

        $expiredValidator = new ExpiredValidator(0);

        $this->assertTrue($expiredValidator->isTokenValid($freshToken), 'Fresh token');
        $this->assertFalse($expiredValidator->isTokenValid($expiredToken), 'Expired token');
    }

    /**
     * Проверяет, что валидатор корректно проверяет время истечения токена
     * с учетом таймаута.
     */
    public function testIsTokenValidWithExpiredTimeout()
    {
        $timeout = mt_rand();
        $freshToken = $this->createToken(time() + $timeout + 1);
        $borderExpiredToken = $this->createToken(time() + $timeout);
        $expiredToken = $this->createToken(time() - 1);

        $expiredValidator = new ExpiredValidator($timeout);

        $this->assertTrue($expiredValidator->isTokenValid($freshToken), 'Fresh token');
        $this->assertTrue($expiredValidator->isTokenValid($borderExpiredToken), 'Border for fresh token');
        $this->assertFalse($expiredValidator->isTokenValid($expiredToken), 'Expired token');
    }
}
