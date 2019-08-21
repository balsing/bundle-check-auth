<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\Jwt\Validator;

use Youtool\AuthBundle\Tests\JwtCase;
use Youtool\AuthBundle\Jwt\Validator\AuthServiceValidator;
use Youtool\AuthBundle\Service\AuthServiceInterface;

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
