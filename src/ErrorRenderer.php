<?php

namespace Alexandria;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

class ErrorRenderer implements  ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $payload = ['error' => $exception->getMessage(), 'code' => $exception->getCode()];
        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}