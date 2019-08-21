<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\Jwt\Validator;

use Youtool\AuthBundle\Tests\JwtCase;
use Youtool\AuthBundle\Jwt\Validator\RequiredScopesValidator;

/**
 * Набор тестов для объекта, который проверяет, что jwt еще не истек.
 */
class RequiredScopesValidatorTest extends JwtCase
{
    /**
     * Проверяет, что валидатор корректно проверяет вермя истечения токена.
     */
    public function testIsTokenValid()
    {
        $scope1 = $this->createFakeData()->unique()->word;
        $scope2 = $this->createFakeData()->unique()->word;
        $scope3 = $this->createFakeData()->unique()->word;

        $tokenWithAllScopes = $this->createToken(time(), [$scope1, $scope2]);
        $tokenWithoutAllScopes = $this->createToken(time(), [$scope1, $scope3]);

        $requiredScopesValidator = new RequiredScopesValidator([$scope1, $scope2]);

        $this->assertTrue(
            $requiredScopesValidator->isTokenValid($tokenWithAllScopes),
            'All scopes in token'
        );
        $this->assertFalse(
            $requiredScopesValidator->isTokenValid($tokenWithoutAllScopes),
            'Not all scopes in token'
        );
    }
}
