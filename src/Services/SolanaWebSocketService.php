<?php
namespace Jacknguyen\Crypto\Services;

use WebSocket\Client;
use Illuminate\Support\Facades\Log;

class SolanaWebSocketService
{
    private $client;
    private $wallet_address; // Địa chỉ ví của sàn

    public function __construct()
    {
        $this->client = new Client("wss://api.devnet.solana.com");
        $this->wallet_address = env('SOLANA_WALLET_ADDRESS');
    }

    public function subscribeTransactions()
    {
        // Subscribe để nhận thông báo block mới
        $subscription = json_encode([
            "jsonrpc" => "2.0",
            "id" => 1,
            "method" => "blockSubscribe",
            "params" => [
                ["mentionsAccountOrProgram" => $this->wallet_address],
                ["commitment" => "confirmed"]
            ]
        ]);

        $this->client->send($subscription);

        while (true) {
            try {
                $message = $this->client->receive();
                $this->processMessage(json_decode($message, true));
            } catch (\Exception $e) {
                Log::error("WebSocket error: " . $e->getMessage());
                sleep(5); // Đợi 5s trước khi thử lại
            }
        }
    }

    private function processMessage($message)
    {
        if (!isset($message['params']['result']['value'])) {
            return;
        }

        $block = $message['params']['result']['value'];

        // Process transactions trong block
        foreach ($block['transactions'] as $transaction) {
            if ($this->isDepositTransaction($transaction)) {
                $this->processDeposit($transaction);
            }
        }
    }

    private function isDepositTransaction($transaction)
    {
        // Kiểm tra xem transaction có phải là deposit vào ví của sàn không
        return $transaction['to'] === $this->wallet_address;
    }

    private function processDeposit($transaction)
    {
        // Lưu transaction vào database
        \App\Models\SolanaTransaction::create([
            'signature' => $transaction['signature'],
            'from_address' => $transaction['from'],
            'to_address' => $transaction['to'],
            'amount' => $transaction['amount'],
            'status' => 'completed',
            'block_time' => now()
        ]);
    }
}
