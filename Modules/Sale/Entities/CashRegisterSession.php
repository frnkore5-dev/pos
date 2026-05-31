<?php

namespace Modules\Sale\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class CashRegisterSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public static function openSessionForUser(int $userId): ?self
    {
        return static::query()
            ->where('user_id', $userId)
            ->whereNull('closed_at')
            ->orderByDesc('opened_at')
            ->first();
    }

    /**
     * Opening float + cash payments recorded for sales in this session (minor units).
     */
    public function computeExpectedCashCents(): int
    {
        $opening = (int) ($this->attributes['opening_amount'] ?? 0);

        return $opening + $this->computeCashPaymentsCents();
    }

    public function computeCashPaymentsCents(): int
    {
        return (int) DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.cash_register_session_id', $this->id)
            ->where('sale_payments.payment_method', 'Cash')
            ->sum('sale_payments.amount');
    }

    public function computePaymentTotalsCents(): array
    {
        $rows = DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.cash_register_session_id', $this->id)
            ->select('sale_payments.payment_method', DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('sale_payments.payment_method')
            ->get();

        $cash = 0;
        $card = 0;
        $other = 0;

        foreach ($rows as $row) {
            if ($row->payment_method === 'Cash') {
                $cash = (int) $row->total;
            } elseif ($row->payment_method === 'Credit Card') {
                $card = (int) $row->total;
            } else {
                $other += (int) $row->total;
            }
        }

        return compact('cash', 'card', 'other');
    }

    public function computePendingCents(): int
    {
        return (int) DB::table('sales')
            ->where('cash_register_session_id', $this->id)
            ->sum('due_amount');
    }

    public function buildCloseSummary(): array
    {
        $openingCents = (int) ($this->attributes['opening_amount'] ?? 0);
        $payments = $this->computePaymentTotalsCents();
        $pendingCents = $this->computePendingCents();

        return [
            'opening_cents' => $openingCents,
            'cash_cents' => $payments['cash'],
            'card_cents' => $payments['card'],
            'other_cents' => $payments['other'],
            'pending_cents' => $pendingCents,
            'expected_cents' => $openingCents + $payments['cash'],
            'sales_count' => (int) DB::table('sales')->where('cash_register_session_id', $this->id)->count(),
            'total_sales_cents' => (int) DB::table('sales')->where('cash_register_session_id', $this->id)->sum('total_amount'),
        ];
    }

    public function setOpeningAmountAttribute($value): void
    {
        $this->attributes['opening_amount'] = (int) round(((float) $value) * 100);
    }

    public function getOpeningAmountAttribute($value): float
    {
        return ((int) $value) / 100;
    }

    public function setClosingAmountCountedAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['closing_amount_counted'] = null;

            return;
        }
        $this->attributes['closing_amount_counted'] = (int) round(((float) $value) * 100);
    }

    public function getClosingAmountCountedAttribute($value): ?float
    {
        if ($value === null) {
            return null;
        }

        return ((int) $value) / 100;
    }

    public function getExpectedCashAmountAttribute($value): ?float
    {
        if ($value === null) {
            return null;
        }

        return ((int) $value) / 100;
    }

    public function getCashDifferenceAttribute($value): ?float
    {
        if ($value === null) {
            return null;
        }

        return ((int) $value) / 100;
    }
}
