<?php

namespace App\Http\Controllers\Customer\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WalletController extends Controller
{
    /**
     * Display wallet dashboard
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $wallet = $customer->getOrCreateWallet();

        // Get recent transactions
        $recentTransactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get transaction summary for last 30 days
        $summary = $wallet->getTransactionSummary(30);
        $totalCredits = $summary->where('type', 'credit')->sum('total_amount');
        $totalDebits = $summary->where('type', 'debit')->sum('total_amount');

        return Inertia::render('Customer/Wallet/Index', [
            'wallet' => [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'available_balance' => $wallet->available_balance,
                'reserved_balance' => $wallet->reserved_balance,
                'currency' => $wallet->currency,
                'status' => $wallet->status,
            ],
            'recentTransactions' => $recentTransactions,
            'summary' => [
                'total_credits' => $totalCredits,
                'total_debits' => $totalDebits,
                'net_change' => $totalCredits - $totalDebits,
            ],
        ]);
    }

    /**
     * Show deposit page
     */
    public function deposit()
    {
        $customer = Auth::guard('customer')->user();
        $wallet = $customer->getOrCreateWallet();

        return Inertia::render('Customer/Wallet/Deposit', [
            'wallet' => [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'currency' => $wallet->currency,
            ],
        ]);
    }

    /**
     * Process deposit request
     */
    public function processDeposit(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1|max:10000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $customer = Auth::guard('customer')->user();
            $wallet = $customer->getOrCreateWallet();
            $amount = $request->amount;

            DB::beginTransaction();

            // Create payment record first
            $payment = Payment::create([
                'customer_id' => $customer->id,
                'wallet_id' => $wallet->id,
                'payment_method' => 'card',
                'gateway' => 'paymob',
                'amount' => $amount,
                'currency' => 'AED',
                'status' => 'pending',
                'description' => 'Wallet deposit via Paymob Intention API',
            ]);

            // Generate special reference for tracking
            $specialReference = $payment->payment_id . '-' . time();

            // Prepare billing data
            $billingData = [
                "apartment" => "N/A",
                "first_name" => explode(' ', $customer->name)[0] ?? $customer->name,
                "last_name" => count(explode(' ', $customer->name)) > 1 ? implode(' ', array_slice(explode(' ', $customer->name), 1)) : $customer->name,
                "street" => "N/A",
                "building" => "N/A",
                "phone_number" => $customer->phone ?? "+971000000000",
                "city" => "N/A",
                "country" => "AE",
                "email" => $customer->email,
                "floor" => "N/A",
                "state" => "N/A"
            ];

            // Create Payment Intention using Paymob Intention API
            $callbackUrl = url(route('customer.wallet.deposit.callback'));
            
            $intentionResponse = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Token ' . config('paymob.secret_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://uae.paymob.com/v1/intention/', [
                    'amount' => (int)($amount * 100), // Amount in cents/fils
                    'currency' => 'AED',
                    'payment_methods' => [
                        (int)config('paymob.integration_id')
                    ],
                    'items' => [
                        [
                            'name' => 'Wallet Deposit',
                            'amount' => (int)($amount * 100),
                            'description' => 'Adding money to wallet',
                            'quantity' => 1
                        ]
                    ],
                    'billing_data' => $billingData,
                    'extras' => [
                        'payment_id' => $payment->payment_id,
                        'customer_id' => $customer->id,
                        'wallet_id' => $wallet->id,
                    ],
                    'special_reference' => $specialReference,
                    'notification_url' => url(route('paymob.webhook')),
                    'redirection_url' => $callbackUrl,
                ])->json();

            // Check for errors in response
            if (isset($intentionResponse['detail']) || !isset($intentionResponse['id'])) {
                $errorMessage = $intentionResponse['detail'] ?? $intentionResponse['message'] ?? 'Failed to create payment intention';
                throw new \Exception($errorMessage);
            }

            $intentionId = $intentionResponse['id'];
            $clientSecret = $intentionResponse['client_secret'] ?? null;
            $publicKey = config('paymob.public_key');
            
            if (!$clientSecret || !$publicKey) {
                throw new \Exception('PayMob configuration error. Please check your settings.');
            }
            
            // Build Unified Checkout URL
            $paymentUrl = "https://uae.paymob.com/unifiedcheckout/?publicKey=" . urlencode($publicKey) . 
                          "&clientSecret=" . urlencode($clientSecret) . 
                          "&intention_id=" . urlencode($intentionId);

            // Update payment with intention ID and response
            $payment->update([
                'intention_id' => $intentionId,
                'gateway_order_id' => $intentionId,
                'gateway_response' => $intentionResponse,
                'gateway_metadata' => [
                    'special_reference' => $specialReference,
                    'client_secret' => $clientSecret,
                    'payment_url' => $paymentUrl,
                ]
            ]);

            DB::commit();

            Log::info('Paymob Intention API - Payment intention created', [
                'payment_id' => $payment->payment_id,
                'intention_id' => $intentionId,
                'amount' => $amount,
                'customer_id' => $customer->id,
            ]);

            // Return payment URL for frontend redirect (to avoid CORS issues with Inertia)
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'payment_id' => $payment->payment_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Paymob Intention API deposit initiation failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id ?? null,
                'amount' => $request->amount ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show transactions page
     */
    public function transactions(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $wallet = $customer->getOrCreateWallet();

        $query = $wallet->transactions()->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);

        return Inertia::render('Customer/Wallet/Transactions', [
            'wallet' => [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'available_balance' => $wallet->available_balance,
                'currency' => $wallet->currency,
            ],
            'transactions' => $transactions,
            'filters' => $request->only(['type', 'category', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show withdrawal request page
     */
    public function withdrawal()
    {
        $customer = Auth::guard('customer')->user();
        $wallet = $customer->getOrCreateWallet();

        return Inertia::render('Customer/Wallet/Withdrawal', [
            'wallet' => [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'available_balance' => $wallet->available_balance,
                'currency' => $wallet->currency,
            ],
        ]);
    }

    /**
     * Process withdrawal request
     */
    public function processWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_account' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $customer = Auth::guard('customer')->user();
            $wallet = $customer->getOrCreateWallet();

            if (!$wallet->hasSufficientBalance($request->amount)) {
                return redirect()->back()->withErrors([
                    'amount' => 'Insufficient wallet balance. Available: ' . $wallet->available_balance . ' ' . $wallet->currency
                ]);
            }

            DB::beginTransaction();

            // Create withdrawal transaction
            $transaction = $wallet->debit(
                $request->amount,
                'Withdrawal to ' . $request->bank_name . ' - ' . $request->bank_account,
                'withdrawal',
                null,
                [
                    'bank_account' => $request->bank_account,
                    'bank_name' => $request->bank_name,
                    'account_holder_name' => $request->account_holder_name,
                    'status' => 'pending_approval',
                    'description' => $request->description
                ]
            );

            DB::commit();

            return redirect()->route('customer:wallet.index')
                ->with('success', 'Withdrawal request submitted successfully. It will be processed within 1-2 business days.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal request failed', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Withdrawal request failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle Paymob callback (web redirect)
     */
    public function depositCallback(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Paymob web callback received', [
                'method' => $request->method(),
                'data' => $data,
            ]);

            // Find payment by intention_id
            $payment = null;
            
            if ($request->has('intention_id')) {
                $payment = Payment::where('intention_id', $request->intention_id)->first();
            } elseif (isset($data['intention_id'])) {
                $payment = Payment::where('intention_id', $data['intention_id'])->first();
            } elseif (isset($data['obj']['id'])) {
                $payment = Payment::where('intention_id', $data['obj']['id'])->first();
            }

            if (!$payment) {
                return redirect()->route('customer:wallet.deposit')
                    ->withErrors(['error' => 'Payment not found']);
            }

            // Check payment success status
            $isSuccess = false;
            $isConfirmed = false;
            
            if (isset($data['success'])) {
                $isSuccess = $data['success'] === true || $data['success'] === "true" || $data['success'] === 1;
            } elseif (isset($data['obj']['success'])) {
                $isSuccess = $data['obj']['success'] === true || $data['obj']['success'] === "true" || $data['obj']['success'] === 1;
            }
            
            if (isset($data['confirmed'])) {
                $isConfirmed = $data['confirmed'] === true || $data['confirmed'] === "true" || $data['confirmed'] === 1;
            } elseif (isset($data['obj']['confirmed'])) {
                $isConfirmed = $data['obj']['confirmed'] === true || $data['obj']['confirmed'] === "true" || $data['obj']['confirmed'] === 1;
            }

            if ($isSuccess && $isConfirmed) {
                return redirect()->route('customer:wallet.index')
                    ->with('success', 'Payment completed successfully! Your wallet has been credited.');
            } else {
                return redirect()->route('customer:wallet.deposit')
                    ->withErrors(['error' => 'Payment failed. Please try again.']);
            }

        } catch (\Exception $e) {
            Log::error('Web callback processing failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return redirect()->route('customer:wallet.deposit')
                ->withErrors(['error' => 'An error occurred while processing your payment.']);
        }
    }
}
