<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;

final class InvoicePaymentSyncService
{
    public function sync(Invoice $invoice): void
    {
        $paidAmount = (float) $invoice->payments()
            ->where('status', PaymentStatus::PAID->value)
            ->sum('amount');

        $total = (float) $invoice->total;
        $balanceDue = max(0, round($total - $paidAmount, 2));
        $status = $this->statusFor($invoice, $paidAmount, $balanceDue);

        $invoice->forceFill([
            'balance_due' => $balanceDue,
            'status' => $status->value,
        ])->saveQuietly();
    }

    private function statusFor(Invoice $invoice, float $paidAmount, float $balanceDue): InvoiceStatus
    {
        if ($invoice->status === InvoiceStatus::CANCELLED) {
            return InvoiceStatus::CANCELLED;
        }

        if ($paidAmount > 0 && $balanceDue <= 0) {
            return InvoiceStatus::PAID;
        }

        if ($paidAmount > 0) {
            return InvoiceStatus::PARTIALLY_PAID;
        }

        if ($invoice->due_date !== null && $invoice->due_date->isPast() && ! $invoice->due_date->isToday()) {
            return InvoiceStatus::OVERDUE;
        }

        if ($invoice->status === InvoiceStatus::DRAFT) {
            return InvoiceStatus::DRAFT;
        }

        return InvoiceStatus::SENT;
    }
}
