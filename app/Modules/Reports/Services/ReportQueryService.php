<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Enums\CustomerStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TicketStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Reports\DTOs\ReportDateRangeData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ReportQueryService
{
    /**
     * @return array<string, int|float>
     */
    public function kpis(?User $user, ReportDateRangeData $range): array
    {
        return Cache::remember($this->cacheKey($user, $range, 'kpis'), now()->addMinutes(10), function () use ($user, $range): array {
            $totalRevenue = (float) $this->payments($user)
                ->where('status', PaymentStatus::PAID->value)
                ->sum('amount');
            $monthlyRevenue = (float) $this->payments($user)
                ->where('status', PaymentStatus::PAID->value)
                ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount');
            $totalCustomers = $this->customers($user)->count();

            return [
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'average_revenue_per_customer' => $totalCustomers > 0 ? round($totalRevenue / $totalCustomers, 2) : 0.0,
                'total_customers' => $totalCustomers,
                'active_customers' => $this->customers($user)->where('status', CustomerStatus::ACTIVE->value)->count(),
                'new_customers_this_month' => $this->customers($user)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'total_subscriptions' => $this->subscriptions($user)->count(),
                'active_subscriptions' => $this->subscriptions($user)->where('status', SubscriptionStatus::ACTIVE->value)->count(),
                'expiring_soon' => $this->subscriptions($user)
                    ->whereDate('end_date', '>=', today())
                    ->whereDate('end_date', '<=', today()->addDays(7))
                    ->count(),
                'total_invoices' => $this->invoices($user)->count(),
                'outstanding_balance' => (float) $this->invoices($user)->sum('balance_due'),
                'outstanding_invoices' => $this->invoices($user)->where('balance_due', '>', 0)->count(),
                'total_payments' => $this->payments($user)->count(),
                'open_tickets' => $this->tickets($user)->where('status', TicketStatus::OPEN->value)->count(),
            ];
        });
    }

    /**
     * @return array<int, array{month: string, total: float}>
     */
    public function revenueLast12Months(?User $user): array
    {
        return $this->monthlySum($this->payments($user)->where('status', PaymentStatus::PAID->value), 'payment_date', 'amount');
    }

    /**
     * @return array<int, array{month: string, total: int}>
     */
    public function customerGrowth(?User $user): array
    {
        return $this->monthlyCount($this->customers($user), 'created_at');
    }

    /**
     * @return array<int, array{month: string, total: int}>
     */
    public function subscriptionGrowth(?User $user): array
    {
        return $this->monthlyCount($this->subscriptions($user), 'created_at');
    }

    /**
     * @return array<string, float>
     */
    public function paymentsByMethod(?User $user, ?ReportDateRangeData $range = null): array
    {
        $query = $this->payments($user)->where('status', PaymentStatus::PAID->value);
        $this->applyDateRange($query, 'payment_date', $range);

        return $query
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->map(fn (mixed $total): float => (float) $total)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function ticketStatusDistribution(?User $user): array
    {
        return $this->tickets($user)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function ticketPriorityDistribution(?User $user): array
    {
        return $this->tickets($user)
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function rows(string $report, ?User $user, ReportDateRangeData $range): array
    {
        return match ($report) {
            'revenue', 'payment' => $this->payments($user)->with(['customer', 'subscription'])
                ->whereBetween('payment_date', [$range->startDate->toDateString(), $range->endDate->toDateString()])
                ->latest('payment_date')
                ->limit(5000)
                ->get()
                ->map(fn (Payment $payment): array => [
                    'date' => $payment->payment_date?->toDateString(),
                    'customer' => $payment->customer?->full_name,
                    'subscription' => $payment->subscription?->plan_name,
                    'method' => $payment->payment_method?->label(),
                    'status' => $payment->status?->label(),
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                ])->all(),
            'customer' => $this->customers($user)
                ->whereBetween('created_at', [$range->startDate, $range->endDate])
                ->latest()
                ->limit(5000)
                ->get(['full_name', 'phone', 'email', 'country', 'status', 'created_at'])
                ->map(fn (Customer $customer): array => [
                    'name' => $customer->full_name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'country' => $customer->country,
                    'status' => $customer->status?->label(),
                    'created_at' => $customer->created_at?->toDateTimeString(),
                ])->all(),
            'subscription' => $this->subscriptions($user)->with('customer')
                ->whereBetween('created_at', [$range->startDate, $range->endDate])
                ->latest()
                ->limit(5000)
                ->get()
                ->map(fn (Subscription $subscription): array => [
                    'customer' => $subscription->customer?->full_name,
                    'plan' => $subscription->plan_name,
                    'status' => $subscription->status?->label(),
                    'start_date' => $subscription->start_date?->toDateString(),
                    'end_date' => $subscription->end_date?->toDateString(),
                    'price' => $subscription->price,
                    'currency' => $subscription->currency,
                ])->all(),
            'invoice' => $this->invoices($user)->with('customer')
                ->whereBetween('issue_date', [$range->startDate->toDateString(), $range->endDate->toDateString()])
                ->latest('issue_date')
                ->limit(5000)
                ->get()
                ->map(fn (Invoice $invoice): array => [
                    'invoice_number' => $invoice->invoice_number,
                    'customer' => $invoice->customer?->full_name,
                    'issue_date' => $invoice->issue_date?->toDateString(),
                    'due_date' => $invoice->due_date?->toDateString(),
                    'status' => $invoice->status?->label(),
                    'total' => $invoice->total,
                    'balance_due' => $invoice->balance_due,
                    'currency' => $invoice->currency,
                ])->all(),
            'ticket' => $this->tickets($user)->with(['customer', 'assignee'])
                ->whereBetween('created_at', [$range->startDate, $range->endDate])
                ->latest()
                ->limit(5000)
                ->get()
                ->map(fn (Ticket $ticket): array => [
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'customer' => $ticket->customer?->full_name,
                    'assigned_to' => $ticket->assignee?->name,
                    'priority' => $ticket->priority?->label(),
                    'status' => $ticket->status?->label(),
                    'created_at' => $ticket->created_at?->toDateTimeString(),
                ])->all(),
            default => [],
        };
    }

    /**
     * @return Builder<Customer>
     */
    private function customers(?User $user): Builder
    {
        return $this->tenant(Customer::query(), $user);
    }

    /**
     * @return Builder<Subscription>
     */
    private function subscriptions(?User $user): Builder
    {
        return $this->tenant(Subscription::query(), $user);
    }

    /**
     * @return Builder<Invoice>
     */
    private function invoices(?User $user): Builder
    {
        return $this->tenant(Invoice::query(), $user);
    }

    /**
     * @return Builder<Payment>
     */
    private function payments(?User $user): Builder
    {
        return $this->tenant(Payment::query(), $user);
    }

    /**
     * @return Builder<Ticket>
     */
    private function tickets(?User $user): Builder
    {
        return $this->tenant(Ticket::query(), $user);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @param Builder<TModel> $query
     * @return Builder<TModel>
     */
    private function tenant(Builder $query, ?User $user): Builder
    {
        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('agency_id', $user?->agency_id ?? 0);
    }

    private function applyDateRange(Builder $query, string $column, ?ReportDateRangeData $range): void
    {
        if ($range !== null) {
            $query->whereBetween($column, [$range->startDate->toDateString(), $range->endDate->toDateString()]);
        }
    }

    /**
     * @return array<int, array{month: string, total: float}>
     */
    private function monthlySum(Builder $query, string $dateColumn, string $amountColumn): array
    {
        return $query
            ->where($dateColumn, '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month, SUM({$amountColumn}) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row): array => ['month' => $row->month, 'total' => (float) $row->total])
            ->all();
    }

    /**
     * @return array<int, array{month: string, total: int}>
     */
    private function monthlyCount(Builder $query, string $dateColumn): array
    {
        return $query
            ->where($dateColumn, '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row): array => ['month' => $row->month, 'total' => (int) $row->total])
            ->all();
    }

    private function cacheKey(?User $user, ReportDateRangeData $range, string $metric): string
    {
        return sprintf(
            'reports:%s:%s:%s:%s:%s',
            $metric,
            $user?->isSuperAdmin() ? 'super' : 'agency',
            $user?->isSuperAdmin() ? 'all' : (string) ($user?->agency_id ?? 0),
            $range->startDate->toDateString(),
            $range->endDate->toDateString(),
        );
    }
}
