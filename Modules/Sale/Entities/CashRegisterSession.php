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

        $cashPaymentsSum = (int) DB::table('sale_payments')
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.cash_register_session_id', $this->id)
            ->where('sale_payments.payment_method', 'Cash')
            ->sum('sale_payments.amount');

        return $opening + $cashPaymentsSum;
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
