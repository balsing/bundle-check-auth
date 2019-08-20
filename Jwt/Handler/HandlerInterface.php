<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Jwt\Handler;

use YouTool\AuthBundle\Jwt\Parser\ParserInterface;
use YouTool\AuthBundle\Jwt\Validator\ValidatorInterface;

/**
 * Интерфейс для объекта обработчика токена: сочетает в себе функционал
 * как парсера, так и валидации.
 */
interface HandlerInterface extends ValidatorInterface, ParserInterface
{
}
