<?php

declare(strict_types=1);

namespace App\StateMachines\Exceptions;

use RuntimeException;

class InvalidTransitionException extends RuntimeException
{
    public function __construct(string $from, string $to, string $modelClass)
    {
        $model = class_basename($modelClass);
        parent::__construct(
            "Transición inválida en {$model}: no se puede pasar de \"{$from}\" a \"{$to}\"."
        );
    }
}