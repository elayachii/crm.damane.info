<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Services;

use App\Models\Invoice;

final class InvoiceCalculator
{
    public function applyTotals(Invoice $invoice): void
    {
        $subtotal = $this->money($invoice->subtotal);
        $tax = $this->money($invoice->tax);
        $discount = $this->money($invoice->discount);

        $invoice->total = max(0, $subtotal + $tax - $discount);

        if (! $invoice->exists) {
            $invoice->balance_due = $invoice->total;
        }
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
