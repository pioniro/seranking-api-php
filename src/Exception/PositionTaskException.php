<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;

use Throwable;

class PositionTaskException extends SerankingException
{
    const MESSAGE_NEW_TASK_CONTAIN_ID = 'new task could not contain id';
    const MESSAGE_NEW_TASK_MUST_CONTAIN_QUERY = 'new task must contain query';
    const MESSAGE_NEW_TASK_MUST_CONTAIN_ENGINE = 'new task must contain engine';

    public static function newTaskContainId(
        $message = self::MESSAGE_NEW_TASK_CONTAIN_ID,
        $code = 0,
        Throwable $previous = null
    ): self {
        return new static($message, $code, $previous);
    }

    public static function newTaskNotContainQuery(
        $message = self::MESSAGE_NEW_TASK_MUST_CONTAIN_QUERY,
        $code = 0,
        Throwable $previous = null
    ): self {
        return new static($message, $code, $previous);
    }

    public static function newTaskNotContainEngine(
        $message = self::MESSAGE_NEW_TASK_MUST_CONTAIN_ENGINE,
        $code = 0,
        Throwable $previous = null
    ): self {
        return new static($message, $code, $previous);
    }
}