<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Exception;

class SerankingApiException extends SerankingException
{
    const MESSAGE = 'Seranking API return unexpected code';
}