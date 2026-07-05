<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Services;

use App\Models\Invoice;
use Illuminate\Support\Str;

final class InvoiceNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        } while (Invoice::query()->withTrashed()->where('invoice_number', $number)->exists());

        return $number;
    }
}
