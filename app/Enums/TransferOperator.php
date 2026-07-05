<?php

declare(strict_types=1);

namespace App\Enums;

enum TransferOperator: string
{
    case WESTERN_UNION = 'western_union';
    case RIA = 'ria';
    case MONEYGRAM = 'moneygram';
    case DAMANE_CASH = 'damane_cash';
    case CASH_PLUS = 'cash_plus';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WESTERN_UNION => 'Western Union',
            self::RIA => 'Ria',
            self::MONEYGRAM => 'MoneyGram',
            self::DAMANE_CASH => 'Damane Cash',
            self::CASH_PLUS => 'Cash Plus',
            self::OTHER => 'Other',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $operator): array => [$operator->value => $operator->label()])
            ->all();
    }
}
