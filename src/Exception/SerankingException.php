<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;

use RuntimeException;
use Throwable;

abstract class SerankingException extends RuntimeException
{
    const MESSAGE = 'Unexpected exception in seranking library';

    public function __construct($message = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?? static::MESSAGE, $code, $previous);
    }
}