<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    public const STATUSES = [
        'CREATED' => 'Iniciada (sin pagar)',
        'PENDING' => 'Pendiente',
        'APPROVED' => 'Aprobada',
        'DECLINED' => 'Rechazada',
        'VOIDED' => 'Anulada',
        'ERROR' => 'Error',
    ];

    /** Estados que Wompi ya no cambia. */
    public const FINAL_STATUSES = ['APPROVED', 'DECLINED', 'VOIDED', 'ERROR'];

    protected $fillable = [
        'reference', 'amount_in_cents', 'currency', 'status', 'wompi_transaction_id', 'payment_method_type',
        'full_name', 'document_type', 'document_number', 'email', 'phone',
        'terms_accepted_at', 'ip_address', 'source', 'paid_at', 'status_updated_at', 'last_event',
    ];

    protected $casts = [
        'amount_in_cents' => 'integer',
        'terms_accepted_at' => 'datetime',
        'paid_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'last_event' => 'array',
    ];

    public static function newReference(): string
    {
        return 'LR-'.now()->format('ymd').'-'.Str::upper(Str::random(10));
    }

    public function getAmountAttribute(): int
    {
        return intdiv($this->amount_in_cents, 100);
    }

    public function getAmountFormattedAttribute(): string
    {
        return static::money($this->amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public static function money(int|float $pesos): string
    {
        return '$'.number_format($pesos, 0, ',', '.');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Aplica un estado reportado por Wompi. Un estado final no se reemplaza
     * por uno anterior (los eventos pueden llegar desordenados o repetidos),
     * salvo APPROVED → VOIDED, que sí es una transición real.
     */
    public function applyWompiStatus(string $status, array $transaction = [], ?array $event = null): bool
    {
        $status = strtoupper($status);
        if (! array_key_exists($status, self::STATUSES) || $status === $this->status) {
            return false;
        }

        $isFinal = in_array($this->status, self::FINAL_STATUSES, true);
        if ($isFinal && ! ($this->status === 'APPROVED' && $status === 'VOIDED')) {
            return false;
        }

        $this->status = $status;
        $this->status_updated_at = now();
        $this->wompi_transaction_id = $transaction['id'] ?? $this->wompi_transaction_id;
        $this->payment_method_type = $transaction['payment_method_type'] ?? $this->payment_method_type;
        if ($status === 'APPROVED') {
            $this->paid_at = $this->paid_at ?? now();
        }
        if ($event !== null) {
            $this->last_event = $event;
        }

        return $this->save();
    }
}
