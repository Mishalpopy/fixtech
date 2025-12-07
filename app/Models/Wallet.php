<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'balance',
        'reserved_balance',
        'currency',
        'status',
        'settings',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'settings' => 'array',
        'last_transaction_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->currency)) {
                $model->currency = 'AED';
            }
        });
    }

    /**
     * Get the customer who owns this wallet
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all transactions for this wallet
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get payments associated with this wallet
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get available balance (total balance minus reserved)
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->reserved_balance;
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance($amount)
    {
        return $this->available_balance >= $amount;
    }

    /**
     * Add funds to wallet
     */
    public function credit($amount, $description, $category = 'deposit', $reference = null, $metadata = null)
    {
        return $this->createTransaction('credit', $amount, $description, $category, $reference, $metadata);
    }

    /**
     * Deduct funds from wallet
     */
    public function debit($amount, $description, $category = 'payment', $reference = null, $metadata = null)
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance');
        }

        return $this->createTransaction('debit', $amount, $description, $category, $reference, $metadata);
    }

    /**
     * Reserve funds for pending transaction
     */
    public function reserve($amount, $description, $reference = null, $metadata = null)
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient wallet balance');
        }

        return $this->createTransaction('reserve', $amount, $description, 'reservation', $reference, $metadata);
    }

    /**
     * Release reserved funds
     */
    public function release($amount, $description, $reference = null, $metadata = null)
    {
        return $this->createTransaction('release', $amount, $description, 'release', $reference, $metadata);
    }

    /**
     * Create a wallet transaction
     */
    protected function createTransaction($type, $amount, $description, $category, $reference = null, $metadata = null)
    {
        $balanceBefore = $this->balance;
        $reservedBefore = $this->reserved_balance;

        // Calculate new balances
        switch ($type) {
            case 'credit':
                $newBalance = $balanceBefore + $amount;
                $newReserved = $reservedBefore;
                break;
            case 'debit':
                $newBalance = $balanceBefore - $amount;
                $newReserved = $reservedBefore;
                break;
            case 'reserve':
                $newBalance = $balanceBefore;
                $newReserved = $reservedBefore + $amount;
                break;
            case 'release':
                $newBalance = $balanceBefore;
                $newReserved = $reservedBefore - $amount;
                break;
            case 'refund':
                $newBalance = $balanceBefore + $amount;
                $newReserved = $reservedBefore;
                break;
            default:
                throw new \Exception('Invalid transaction type');
        }

        // Create transaction record
        $transaction = $this->transactions()->create([
            'transaction_id' => 'WLT-' . strtoupper(Str::random(12)),
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $newBalance,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'metadata' => $metadata,
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        // Update wallet balances
        $this->update([
            'balance' => $newBalance,
            'reserved_balance' => $newReserved,
            'last_transaction_at' => now(),
        ]);

        return $transaction;
    }

    /**
     * Scope for active wallets
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get transaction summary for a period
     */
    public function getTransactionSummary($days = 30)
    {
        $startDate = now()->subDays($days);
        
        return $this->transactions()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                type,
                category,
                COUNT(*) as count,
                SUM(amount) as total_amount
            ')
            ->groupBy('type', 'category')
            ->get();
    }
}
