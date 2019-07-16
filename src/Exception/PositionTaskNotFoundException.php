<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;

use Throwable;

class PositionTaskNotFoundException extends SerankingApiException
{
    const MESSAGE = 'Task not found';

    public function __construct($message = null, $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}