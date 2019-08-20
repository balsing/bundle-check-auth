<?php

declare(strict_types=1);

namespace YouTool\AuthBundle\Exception;

use Exception;

/**
 * Исключение, которое выбрасывается при какой-либо ошибке обработки токена.
 */
class InvalidTokenException extends Exception
{
}
