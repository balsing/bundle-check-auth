<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Jwt\Handler;

use Youtool\AuthBundle\Jwt\Parser\ParserInterface;
use Youtool\AuthBundle\Jwt\Validator\ValidatorInterface;

/**
 * Интерфейс для объекта обработчика токена: сочетает в себе функционал
 * как парсера, так и валидации.
 */
interface HandlerInterface extends ValidatorInterface, ParserInterface
{
}
