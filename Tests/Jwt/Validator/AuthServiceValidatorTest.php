<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Tests\Jwt\Validator;

use YouTool\AuthBundle\Tests\JwtCase;
use YouTool\AuthBundle\Jwt\Validator\AuthServiceValidator;
use YouTool\AuthBundle\Service\AuthServiceInterface;

/**
 * Набор тестов для объекта, который проверяет валидность jwt с помощью запроса
 * к сервису авторизации.
 */
class AuthServiceValidatorTest extends JwtCase
{
    /**
     * Проверяет, что валидатор корректно проверяет время истечения токена.
     */
    public function testIsTokenValid()
    {
        $properToken = $this->createToken();
        $badToken = $this->createToken();

        $service = $this->getMockBuilder(AuthServiceInterface::class)->getMock();
        $service->method('isTokenValid')->will($this->returnCallback(function ($tokenToTest) use ($properToken) {
            return $properToken === $tokenToTest;
        }));

        $authServiceValidator = new AuthServiceValidator($service);

        $this->assertTrue($authServiceValidator->isTokenValid($properToken), 'Valid token');
        $this->assertFalse($authServiceValidator->isTokenValid($badToken), 'Invalid token');
    }
}
