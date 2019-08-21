<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\CryptKey;

use InvalidArgumentException;

/**
 * Объект, который хранит информацию о ключе шифрования.
 */
class CryptKey implements CryptKeyInterface
{
    /**
     * @var string
     */
    protected $keyPath = '';
    /**
     * @var string
     */
    protected $passPhrase = '';

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $keyPath, string $passPhrase = '')
    {
        if (strpos($keyPath, 'file://') !== 0) {
            $keyPath = 'file://' . $keyPath;
        }

        if (!file_exists($keyPath) || !is_readable($keyPath)) {
            throw new InvalidArgumentException(
                "Key path {$keyPath} does not exist or is not readable."
            );
        }

        $this->keyPath = $keyPath;
        $this->passPhrase = $passPhrase;
    }

    /**
     * @inheritdoc
     */
    public function getKeyPath(): string
    {
        return $this->keyPath;
    }

    /**
     * @inheritdoc
     */
    public function getPassPhrase(): string
    {
        return $this->passPhrase;
    }
}
