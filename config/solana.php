<?php
return [
    'network' => env('SOLANA_NETWORK', 'devnet'),
    'websocket_endpoint' => env('SOLANA_WEBSOCKET_ENDPOINT', 'wss://api.devnet.solana.com'),
    'http_endpoint' => env('SOLANA_HTTP_ENDPOINT', 'https://api.devnet.solana.com'),
];
