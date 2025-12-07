<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Get wallet balance for authenticated user
     */
    public function getWalletBalance(Request $request)
    {
        try {
            $customer = $request->user('sanctum');
            $wallet = $customer->getOrCreateWallet();

            return response()->json([
                'success' => true,
                'data' => [
                    'wallet_id' => $wallet->id,
                    'balance' => $wallet->balance,
                    'available_balance' => $wallet->available_balance,
                    'reserved_balance' => $wallet->reserved_balance,
                    'currency' => $wallet->currency,
                    'status' => $wallet->status,
                    'last_transaction_at' => $wallet->last_transaction_at,
                ],
                'message' => 'Wallet balance retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching wallet balance', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wallet balance'
            ], 500);
        }
    }

    /**
     * Get wallet transactions with pagination and filters
     */
    public function getWalletTransactions(Request $request)
    {
        try {
            $customer = $request->user('sanctum');
            $wallet = $customer->getOrCreateWallet();

            $query = $wallet->transactions()->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $limit = $request->get('limit', 20);
            $transactions = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions->items(),
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                        'from' => $transactions->firstItem(),
                        'to' => $transactions->lastItem(),
                    ]
                ],
                'message' => 'Wallet transactions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching wallet transactions', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wallet transactions'
            ], 500);
        }
    }

    /**
     * Get specific wallet transaction
     */
    public function getWalletTransaction(Request $request, $id)
    {
        try {
            $customer = $request->user('sanctum');
            $wallet = $customer->getOrCreateWallet();

            $transaction = $wallet->transactions()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => ['transaction' => $transaction],
                'message' => 'Transaction retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
    }

    /**
     * Get wallet statistics
     */
    public function getWalletStatistics(Request $request)
    {
        try {
            $customer = $request->user('sanctum');
            $wallet = $customer->getOrCreateWallet();

            $days = $request->get('days', 30);
            $summary = $wallet->getTransactionSummary($days);

            $totalCredits = $summary->where('type', 'credit')->sum('total_amount');
            $totalDebits = $summary->where('type', 'debit')->sum('total_amount');
            $totalTransactions = $summary->sum('count');

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'totals' => [
                        'credits' => $totalCredits,
                        'debits' => $totalDebits,
                        'transactions' => $totalTransactions,
                        'net_change' => $totalCredits - $totalDebits,
                    ],
                    'period_days' => $days,
                    'current_balance' => $wallet->balance,
                    'available_balance' => $wallet->available_balance,
                    'reserved_balance' => $wallet->reserved_balance,
                ],
                'message' => 'Wallet statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching wallet statistics', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wallet statistics'
            ], 500);
        }
    }

    /**
     * Initiate wallet deposit via PayMob Intention API
     */
    public function initiateDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'mobile_callback_url' => 'nullable|url', // For mobile app redirects
        ]);

        try {
            $customer = $request->user('sanctum');
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
            // Reference: https://developers.paymob.com/uae/api-reference-guide/create-intention-payment-api
            $callbackUrl = $request->mobile_callback_url ?? url(route('customer.wallet.deposit.callback'));
            
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
                    'notification_url' => url(route('paymob.webhook')), // Full URL for webhook (server-to-server)
                    'redirection_url' => $callbackUrl, // Full URL for user redirect after payment
                ])->json();

            // Check for errors in response
            if (isset($intentionResponse['detail']) || !isset($intentionResponse['id'])) {
                $errorMessage = $intentionResponse['detail'] ?? $intentionResponse['message'] ?? 'Failed to create payment intention';
                throw new \Exception($errorMessage);
            }

            $intentionId = $intentionResponse['id'];
            $clientSecret = $intentionResponse['client_secret'] ?? null;
            $paymentMethods = $intentionResponse['payment_methods'] ?? [];
            $publicKey = config('paymob.public_key');
            
            // Validate required values
            if (!$clientSecret) {
                throw new \Exception('Client secret not found in PayMob response. Please check your PayMob configuration.');
            }
            
            if (!$publicKey) {
                throw new \Exception('PayMob public key is not configured. Please set PAYMOB_PUBLIC_KEY in your .env file.');
            }
            
            // Build Unified Checkout URL
            // Format: https://uae.paymob.com/unifiedcheckout/?publicKey={publicKey}&clientSecret={clientSecret}&intention_id={intentionId}
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
                    'payment_methods' => $paymentMethods,
                    'payment_url' => $paymentUrl,
                ]
            ]);

            DB::commit();

            Log::info('Paymob Intention API - Payment intention created', [
                'payment_id' => $payment->payment_id,
                'intention_id' => $intentionId,
                'amount' => $amount,
                'customer_id' => $customer->id,
                'payment_url' => $paymentUrl,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->payment_id,
                    'intention_id' => $intentionId,
                    'payment_url' => $paymentUrl,
                    'payment_methods' => $paymentMethods,
                    'amount' => $amount,
                    'currency' => 'AED',
                    'status' => 'pending',
                    'special_reference' => $specialReference,
                ],
                'message' => 'Payment intention created successfully. Redirect user to payment_url to complete payment.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Paymob Intention API deposit initiation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
     * Get deposit status
     */
    public function getDepositStatus(Request $request, $paymentId)
    {
        try {
            $customer = $request->user('sanctum');
            
            $payment = Payment::where('payment_id', $paymentId)
                ->where('customer_id', $customer->id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->payment_id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'intention_id' => $payment->intention_id,
                    'wallet_balance' => $payment->wallet->balance ?? null,
                ],
                'message' => 'Payment status retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment status', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    /**
     * Request withdrawal from wallet
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_account' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $customer = $request->user('sanctum');
            $wallet = $customer->getOrCreateWallet();

            if (!$wallet->hasSufficientBalance($request->amount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient wallet balance',
                    'data' => [
                        'available_balance' => $wallet->available_balance,
                        'requested_amount' => $request->amount
                    ]
                ], 400);
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

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction' => $transaction,
                    'wallet_balance' => $wallet->fresh()->balance
                ],
                'message' => 'Withdrawal request submitted successfully. It will be processed within 1-2 business days.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal request failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal request failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mobile callback handler for PayMob Intention API
     * Handles both GET (Paymob redirects) and POST (mobile app callbacks) requests
     */
    public function mobileCallback(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Paymob mobile callback received', [
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
            
            // Try to find by merchant_order_id (special_reference format: PAY-XXX-TIMESTAMP)
            if (!$payment && $request->has('merchant_order_id')) {
                $merchantOrderId = $request->merchant_order_id;
                $parts = explode('-', $merchantOrderId);
                if (count($parts) >= 2) {
                    $paymentId = $parts[0] . '-' . $parts[1];
                    $payment = Payment::where('payment_id', $paymentId)->first();
                }
            }
            
            // Try to find by extras.payment_id
            if (!$payment && isset($data['extras']['payment_id'])) {
                $payment = Payment::where('payment_id', $data['extras']['payment_id'])->first();
            }

            if (!$payment) {
                Log::error('Payment not found for Paymob mobile callback', [
                    'request_data' => $data,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Check payment success status
            $isSuccess = false;
            $isConfirmed = false;
            
            if (isset($data['success'])) {
                $isSuccess = $data['success'] === true || $data['success'] === "true" || $data['success'] === 1;
            } elseif (isset($data['obj']['success'])) {
                $isSuccess = $data['obj']['success'] === true || $data['obj']['success'] === "true" || $data['obj']['success'] === 1;
            }
            
            // Check for confirmed status
            if (isset($data['confirmed'])) {
                $isConfirmed = $data['confirmed'] === true || $data['confirmed'] === "true" || $data['confirmed'] === 1;
            } elseif (isset($data['obj']['confirmed'])) {
                $isConfirmed = $data['obj']['confirmed'] === true || $data['obj']['confirmed'] === "true" || $data['obj']['confirmed'] === 1;
            }
            
            // Check status field
            if (isset($data['status'])) {
                $isConfirmed = ($data['status'] === 'confirmed' || $data['status'] === 'success' || $data['status'] === 'completed');
            } elseif (isset($data['obj']['status'])) {
                $isConfirmed = ($data['obj']['status'] === 'confirmed' || $data['obj']['status'] === 'success' || $data['obj']['status'] === 'completed');
            }
            
            // Check for txn_response_code APPROVED
            if ($isSuccess && !$isConfirmed) {
                if (isset($data['txn_response_code']) && strtoupper($data['txn_response_code']) === 'APPROVED') {
                    $isConfirmed = true;
                } elseif (isset($data['data_message']) && strtoupper($data['data_message']) === 'APPROVED') {
                    $isConfirmed = true;
                } elseif (isset($data['error_occured']) && $data['error_occured'] === false && $isSuccess) {
                    $isConfirmed = true;
                }
            }

            if ($isSuccess && $isConfirmed) {
                // Payment was successful - update wallet if not already updated
                if ($payment->status !== 'completed') {
                    DB::beginTransaction();
                    
                    try {
                        $transactionId = $data['id'] ?? $data['transaction_id'] ?? $data['obj']['id'] ?? 'MOBILE-' . time();
                        
                        // Mark payment as completed
                        $payment->markAsCompleted($transactionId, $data);

                        // Credit the wallet
                        $wallet = $payment->wallet;
                        $wallet->credit(
                            $payment->amount,
                            'Wallet deposit via Paymob Intention API (Mobile)',
                            'deposit',
                            $payment,
                            [
                                'paymob_transaction_id' => $transactionId,
                                'intention_id' => $data['intention_id'] ?? $data['obj']['id'] ?? $payment->intention_id
                            ]
                        );

                        DB::commit();
                        
                        Log::info('Mobile payment completed and wallet credited', [
                            'payment_id' => $payment->payment_id,
                            'amount' => $payment->amount,
                            'wallet_id' => $wallet->id
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Payment completed successfully!',
                            'data' => [
                                'payment_id' => $payment->payment_id,
                                'amount' => $payment->amount,
                                'new_balance' => $wallet->fresh()->balance
                            ]
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to update wallet in mobile callback', [
                            'error' => $e->getMessage(),
                            'payment_id' => $payment->payment_id
                        ]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => 'Payment completed but wallet update failed'
                        ], 500);
                    }
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment already processed',
                        'data' => [
                            'payment_id' => $payment->payment_id,
                            'status' => $payment->status
                        ]
                    ]);
                }
            } else {
                // Payment failed
                if ($payment->status === 'pending') {
                    $payment->markAsFailed($data);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed. Please try again.'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Mobile callback processing failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment.'
            ], 500);
        }
    }
}

