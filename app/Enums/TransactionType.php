<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case SEND_MONEY = 'send_money';
    case RECEIVE_MONEY = 'receive_money';

    public function label(): string
    {
        return match ($this) {
            self::SEND_MONEY => 'Send Money',
            self::RECEIVE_MONEY => 'Receive Money',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
