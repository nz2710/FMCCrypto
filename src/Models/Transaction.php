<?php
// app/Models/Wallet.php
namespace Jacknguyen\Crypto\Models;

use Jacknguyen\Crypto\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'transaction_hash',
        'block_hash',
        'block_number',
        'type',
        'amount',
        'status',
        'error_message',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:9',
        'processed_at' => 'datetime',
        'block_number' => 'integer'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
