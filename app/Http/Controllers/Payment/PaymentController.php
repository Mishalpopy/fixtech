<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Webhook from Paymob (for server-to-server communication)
     */
    public function webhook(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Paymob Intention API webhook received', $data);

            // Validate HMAC signature for security if present
            if ($request->has('hmac') && config('paymob.hmac')) {
                $hmac = $request->hmac;
                $calculatedHmac = $this->calculateHmac($data);
                
                if (!hash_equals($hmac, $calculatedHmac)) {
                    Log::warning('Paymob webhook HMAC validation failed');
                    return response()->json(['error' => 'Invalid HMAC'], 400);
                }
            }

            // Find the payment record by intention_id
            $payment = null;
            
            if (isset($data['obj']['id'])) {
                $payment = Payment::where('intention_id', $data['obj']['id'])->first();
            }
            
            // Try to find by special_reference if not found
            if (!$payment && isset($data['obj']['special_reference'])) {
                $specialRef = $data['obj']['special_reference'];
                $paymentId = explode('-', $specialRef)[0] ?? null;
                if ($paymentId) {
                    $payment = Payment::where('payment_id', $paymentId)->first();
                }
            }

            if (!$payment) {
                Log::error('Payment not found for Paymob Intention API webhook', [
                    'intention_id' => $data['obj']['id'] ?? null,
                    'request_data' => $data
                ]);
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Check payment status from webhook
            $obj = $data['obj'] ?? $data;
            $isSuccess = $obj['success'] ?? false;
            $isConfirmed = $obj['confirmed'] ?? false;
            $status = $obj['status'] ?? null;

            // Update payment status based on webhook data
            if ($isSuccess && $isConfirmed && $status === 'confirmed') {
                // Payment successful and confirmed - check if not already processed
                if ($payment->status !== 'completed') {
                    DB::beginTransaction();
                    try {
                        $transactionId = $obj['id'] ?? $obj['transaction_id'] ?? null;
                        
                        $payment->markAsCompleted($transactionId, $data);

                        // Credit the wallet
                        $wallet = $payment->wallet;
                        if ($wallet) {
                            $wallet->credit(
                                $payment->amount,
                                'Wallet deposit via Paymob Intention API',
                                'deposit',
                                $payment,
                                [
                                    'paymob_transaction_id' => $transactionId,
                                    'intention_id' => $obj['id'] ?? null
                                ]
                            );
                        }

                        DB::commit();

                        Log::info('Payment completed via Intention API webhook and wallet credited', [
                            'payment_id' => $payment->payment_id,
                            'intention_id' => $obj['id'] ?? null,
                            'amount' => $payment->amount,
                            'wallet_id' => $wallet->id ?? null,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to process payment completion in webhook', [
                            'error' => $e->getMessage(),
                            'payment_id' => $payment->payment_id
                        ]);
                        throw $e;
                    }
                }
            } else {
                // Payment failed or not confirmed
                if ($payment->status === 'pending') {
                    $payment->markAsFailed($data);
                    Log::info('Payment failed via Intention API webhook', [
                        'payment_id' => $payment->payment_id,
                        'status' => $status,
                    ]);
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Paymob Intention API webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Calculate HMAC for Paymob webhook validation
     */
    private function calculateHmac($data)
    {
        $hmacSecret = config('paymob.hmac');
        
        // Paymob HMAC calculation (adjust based on Paymob documentation)
        $string = '';
        $string .= $data['amount_cents'] ?? '';
        $string .= $data['created_at'] ?? '';
        $string .= $data['currency'] ?? '';
        $string .= $data['error_occured'] ?? '';
        $string .= $data['id'] ?? '';
        $string .= $data['integration_id'] ?? '';
        $string .= $data['success'] ?? '';
        // Add other fields as per Paymob documentation
        
        return hash_hmac('sha512', $string, $hmacSecret);
    }
}

