<?php

use Illuminate\Support\Facades\Route;
use JackNguyen\Crypto\Http\Controllers\SolanaTestController;

Route::prefix('solana')->group(function () {
    Route::post('/wallets', [SolanaTestController::class, 'createWallet']);
    Route::get('/wallets/{address}', [SolanaTestController::class, 'getWalletInfo']);
    Route::post('/airdrop', [SolanaTestController::class, 'requestAirdrop']);
    Route::get('/transactions', [SolanaTestController::class, 'getTransactions']);
});

