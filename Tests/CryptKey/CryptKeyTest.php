<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Tests\CryptKey;

use Youtool\AuthBundle\Tests\BaseCase;
use Youtool\AuthBundle\CryptKey\CryptKey;
use InvalidArgumentException;

/**
 * Набор тестов для объекта, который хранит данный о ключе шифрования.
 */
class CryptKeyTest extends BaseCase
{
    /**
     * Проверяет, что объект выбросит исключение
     * при указать путь к несуществующему файлу.
     */
    public function testUnexistedFileException()
    {
        $this->expectException(InvalidArgumentException::class);
        new CryptKey('/123/123/123');
    }

    /**
     * Проверяет, что объект возвращает правильный путь к ключу.
     */
    public function testGetKeyPath()
    {
        $path = __DIR__ . '/_fixture/public.key';

        $cryptKey = new CryptKey($path);

        $this->assertSame("file://{$path}", $cryptKey->getKeyPath());
    }

    /**
     * Проверяет, что объект возвращает пароль для ключа, если указан.
     */
    public function testGetPassPhrase()
    {
        $path = __DIR__ . '/_fixture/public.key';
        $passPhrase = $this->createFakeData()->word();

        $cryptKey = new CryptKey($path, $passPhrase);

        $this->assertSame($passPhrase, $cryptKey->getPassPhrase());
    }
}
