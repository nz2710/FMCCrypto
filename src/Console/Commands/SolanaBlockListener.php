<?php
namespace JackNguyen\Crypto\Console\Commands;

use Illuminate\Console\Command;
use JackNguyen\Crypto\Services\SolanaWebSocketService;
use Illuminate\Support\Facades\Log;

class SolanaBlockListener extends Command
{
    protected $signature = 'solana:listen';
    protected $description = 'Listen for new Solana blocks via WebSocket';

    private $solanaService;

    public function __construct(SolanaWebSocketService $solanaService)
    {
        parent::__construct();
        $this->solanaService = $solanaService;
    }

    public function handle()
    {
        $this->info('Starting Solana block listener...');
        Log::info('Solana block listener started');

        try {
            while (true) {
                try {
                    $this->solanaService->processNewBlocks();
                } catch (\WebSocket\ConnectionException $e) {
                    $this->error('WebSocket connection lost: ' . $e->getMessage());
                    Log::error('WebSocket connection lost: ' . $e->getMessage());
                    sleep(5); // Wait before retry
                    $this->info('Attempting to reconnect...');
                } catch (\Exception $e) {
                    $this->error('Error processing blocks: ' . $e->getMessage());
                    Log::error('Error processing blocks: ' . $e->getMessage());
                    sleep(5);
                }
            }
        } catch (\Exception $e) {
            $this->error('Fatal error in block listener: ' . $e->getMessage());
            Log::error('Fatal error in block listener: ' . $e->getMessage());
            return 1;
        }
    }
}
