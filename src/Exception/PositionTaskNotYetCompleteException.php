<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;

class PositionTaskNotYetCompleteException extends SerankingException
{
    const MESSAGE = 'Position task not yet complete';
}