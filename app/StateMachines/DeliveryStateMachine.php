<?php

declare(strict_types=1);

namespace App\StateMachines;

class DeliveryStateMachine extends BaseStateMachine
{
    public const READY = 'Listo';
    public const ON_DELIVERY = 'En reparto';
    public const FINISHED = 'Finalizado';

    public function validStates(): array
    {
        return [self::READY, self::ON_DELIVERY, self::FINISHED];
    }

    public function transitions(): array
    {
        return [
            self::READY => [self::ON_DELIVERY],
            self::ON_DELIVERY => [self::FINISHED, self::READY],
            self::FINISHED => [],
        ];
    }
}