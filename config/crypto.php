<?php

// config/crypto.php
return [
    'networks' => [
        'solana' => [
            'ws_endpoint' => env('SOLANA_WS_ENDPOINT', 'wss://api.devnet.solana.com'),
            'http_endpoint' => env('SOLANA_HTTP_ENDPOINT', 'https://api.devnet.solana.com'),
            'hot_wallet' => env('SOLANA_HOT_WALLET_ADDRESS'),
            'confirmations_required' => env('SOLANA_CONFIRMATIONS_REQUIRED', 1),
        ]
    ]
];
