<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\Jwt\Validator;

use Youtool\AuthBundle\Tests\JwtCase;
use Youtool\AuthBundle\CryptKey\CryptKey;
use Youtool\AuthBundle\Jwt\Validator\VerifyValidator;

/**
 * Набор тестов для объекта, который проверяет, что jwt еще не истек.
 */
class VerifyValidatorTest extends JwtCase
{
    /**
     * Проверяет, что валидатор корректно проверяет подпись токена.
     */
    public function testIsTokenValid()
    {
        $cryptKey = new CryptKey(__DIR__ . '/../../_fixture/public.key');
        $properToken = $this->restoreTokenFromFile();
        $badToken = $this->restoreTokenFromFile(__DIR__ . '/../../_fixture/token_bad.txt');

        $verifyValidator = new VerifyValidator($cryptKey);

        $this->assertTrue($verifyValidator->isTokenValid($properToken), 'Verified token');
        $this->assertFalse($verifyValidator->isTokenValid($badToken), 'Unverified token');
    }
}
