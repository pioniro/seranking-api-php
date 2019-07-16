<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;


use Throwable;

class BadResponseApiException extends SerankingApiException
{
    public const MESSAGE = 'Bad response';

    public static function invalidRequestField($error, $code = 400, Throwable $previous = null)
    {
        return new static(sprintf('%s: %s', static::MESSAGE, $error), $code, $previous);
    }
}