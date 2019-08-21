<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\CryptKey;

/**
 * Интерфейс для объекта, который хранит информацию о ключе шифрования.
 */
interface CryptKeyInterface
{
    /**
     * Возвращает абсолютный путь до файла с ключом.
     */
    public function getKeyPath(): string;

    /**
     * Возвращает пароль для ключа, если указан.
     */
    public function getPassPhrase(): string;
}
