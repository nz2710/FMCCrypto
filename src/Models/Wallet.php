<?php
// app/Models/Wallet.php
namespace Jacknguyen\Crypto\Models;

use Illuminate\Database\Eloquent\Model;
use Jacknguyen\Crypto\Models\Transaction;

class Wallet extends Model
{
    protected $fillable = [
        'address',
        'balance',
        'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:9',
        'is_active' => 'boolean'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
