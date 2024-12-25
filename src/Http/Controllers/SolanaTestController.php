<?php
namespace JackNguyen\Crypto\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use JackNguyen\Crypto\Models\Wallet;
use JackNguyen\Crypto\Models\Transaction;
use JackNguyen\Crypto\Services\SolanaWebSocketService;
use Illuminate\Routing\Controller;

class SolanaTestController extends Controller
{
    private $solanaService;

    public function __construct(SolanaWebSocketService $solanaService)
    {
        $this->solanaService = $solanaService;
    }

    public function createWallet(Request $request)
    {
        $wallet = Wallet::create([
            'address' => $request->address,
            'balance' => 0,
            'is_active' => true
        ]);


        // Subscribe to the new wallet
        $this->solanaService->subscribeNewWallet($wallet->address);

        return response()->json([
            'success' => true,
            'wallet' => $wallet
        ]);
    }

    public function getWalletInfo($address)
    {
        $wallet = Wallet::where('address', $address)
                       ->with(['transactions' => function($query) {
                           $query->latest()->take(10);
                       }])
                       ->firstOrFail();

        return response()->json([
            'wallet' => $wallet,
            'recent_transactions' => $wallet->transactions
        ]);
    }

    public function requestAirdrop(Request $request)
    {
       try {
           $amount = ($request->amount ?? 1) * 1000000000; // Convert to lamports

           $response = Http::post(config('solana.http_endpoint'), [
               'jsonrpc' => '2.0',
               'id' => 1,
               'method' => 'requestAirdrop',
               'params' => [
                   $request->address,
                   $amount
               ]
           ]);

           if ($response->successful() && isset($response['result'])) {
               return response()->json([
                   'success' => true,
                   'message' => 'Airdrop requested successfully',
                   'signature' => $response['result']
               ]);
           }

           return response()->json([
               'success' => false,
               'message' => 'Airdrop request failed',
               'error' => $response['error'] ?? 'Unknown error'
           ], 400);

       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Airdrop request failed',
               'error' => $e->getMessage()
           ], 500);
       }
    }

    public function getTransactions($address)
    {
        $wallet = Wallet::where('address', $address)
                       ->firstOrFail();

        $transactions = Transaction::where('wallet_id', $wallet->id)
                                 ->latest()
                                 ->paginate(20);

        return response()->json([
            'transactions' => $transactions
        ]);
    }
}
