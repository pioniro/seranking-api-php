<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;

use Throwable;

class EmptyBalanceException extends SerankingApiException
{
    const MESSAGE = 'Empty balance';

    public function __construct($message = null, $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}