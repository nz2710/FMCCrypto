<?php
namespace JackNguyen\Crypto\Services;

use WebSocket\Client;
use Illuminate\Support\Facades\Log;
use Jacknguyen\Crypto\Models\Wallet;
use Jacknguyen\Crypto\Models\Transaction;

class SolanaWebSocketService
{
    private $client;
    private $endpoint;
    private $activeSubscriptions = [];

    public function __construct()
    {
        $this->endpoint = config('solana.websocket_endpoint', 'wss://api.devnet.solana.com');
        $this->initializeWebSocket();
    }

    private function initializeWebSocket()
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $this->client = new Client(
                $this->endpoint,
                [
                    'timeout' => 60,
                    'fragment_size' => 8192,
                    'context' => $context
                ]
            );

            $this->subscribeToWalletTransactions();
        } catch (\Exception $e) {
            Log::error('Solana WebSocket connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function subscribeToWalletTransactions()
    {
        try {
            // Get all active wallets
            $wallets = Wallet::where('is_active', true)->get();

            foreach ($wallets as $wallet) {
                $this->subscribeToWallet($wallet->address);
            }

            Log::info('Successfully subscribed to all active wallets', [
                'wallet_count' => count($wallets)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to subscribe to wallets: ' . $e->getMessage());
            throw $e;
        }
    }

    private function subscribeToWallet(string $address)
    {
        // Subscribe to account notifications
        $accountSubscribe = json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'accountSubscribe',
            'params' => [
                $address,
                [
                    'encoding' => 'jsonParsed',
                    'commitment' => 'confirmed'
                ]
            ]
        ]);

        $this->client->send($accountSubscribe);
        $response = json_decode($this->client->receive(), true);

        if (isset($response['result'])) {
            $this->activeSubscriptions[$address] = $response['result'];
            Log::info('Subscribed to wallet', [
                'address' => $address,
                'subscription_id' => $response['result']
            ]);
        }
    }
    /**
     * Process new blocks continuously
     * @return never
     */
    public function processNewBlocks()
    {
        while (true) {
            try {
                $message = $this->client->receive();
                $data = json_decode($message, true);
                Log::debug('Received WebSocket data', ['data' => $data]);

                if (isset($data['method']) && $data['method'] === 'accountNotification') {
                    $this->processAccountUpdate($data['params']);
                }
            } catch (\WebSocket\ConnectionException $e) {
                Log::error('WebSocket connection error: ' . $e->getMessage());
                sleep(5);
                $this->initializeWebSocket();
            } catch (\Exception $e) {
                Log::error('Error processing: ' . $e->getMessage());
                sleep(5);
            }
        }
    }

    private function processAccountUpdate(array $params)
    {
        try {
            Log::debug('Processing account update', ['params' => $params]);

            // Lấy account address từ subscription mapping
            $accountKey = array_search($params['subscription'], $this->activeSubscriptions);
            if (!$accountKey) {
                Log::warning('Cannot find wallet address for subscription', [
                    'subscription' => $params['subscription']
                ]);
                return;
            }

            $value = $params['result']['value'] ?? null;
            if (!$value) {
                return;
            }

            // Find the associated wallet
            $wallet = Wallet::where('address', $accountKey)
                ->where('is_active', true)
                ->first();

            if (!$wallet) {
                Log::warning('Wallet not found', ['address' => $accountKey]);
                return;
            }

            // Get the previous balance to compare
            $previousBalance = $wallet->balance;
            $newBalance = ($value['lamports'] ?? 0) / 1000000000; // Convert lamports to SOL

            // If balance changed, create a transaction
            if ($previousBalance != $newBalance) {
                $amount = abs($newBalance - $previousBalance);
                $type = $newBalance > $previousBalance ? 'deposit' : 'withdrawal';

                // Generate a unique transaction hash
                $transactionHash = 'SOL_' . time() . '_' . uniqid();

                Log::info('Balance changed', [
                    'wallet' => $accountKey,
                    'previous' => $previousBalance,
                    'new' => $newBalance,
                    'type' => $type,
                    'amount' => $amount,
                    'hash' => $transactionHash
                ]);

                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'transaction_hash' => $transactionHash, // Thêm trường này
                    'type' => $type,
                    'amount' => $amount,
                    'status' => 'completed',
                    'processed_at' => now()
                ]);

                // Update wallet balance
                $wallet->balance = $newBalance;
                $wallet->save();
            }
        } catch (\Exception $e) {
            Log::error('Error processing account update: ' . $e->getMessage(), [
                'params' => $params
            ]);
        }
    }

    public function subscribeNewWallet(string $address)
    {
        $this->subscribeToWallet($address);
    }

    public function unsubscribeWallet(string $address)
    {
        if (isset($this->activeSubscriptions[$address])) {
            $unsubscribe = json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'accountUnsubscribe',
                'params' => [$this->activeSubscriptions[$address]]
            ]);

            $this->client->send($unsubscribe);
            unset($this->activeSubscriptions[$address]);

            Log::info('Unsubscribed from wallet', ['address' => $address]);
        }
    }
}
