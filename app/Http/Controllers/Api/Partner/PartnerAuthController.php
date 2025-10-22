<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Partner\PartnerLoginRequest;
use App\Http\Requests\Api\Partner\PartnerRegisterRequest;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PartnerAuthController extends Controller
{
    /**
     * Register a new partner
     */
    public function register(PartnerRegisterRequest $request): JsonResponse
    {
        try {
            $partner = Partner::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'company_name' => $request->company_name,
                'trade_license_no' => $request->trade_license_no,
                'status' => true, // Active by default
                'approval_status' => 'pending', // Pending approval by default
            ]);

            $token = $partner->createToken('partner-token', ['partner'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Partner registered successfully. Your account is pending approval.',
                'data' => [
                    'partner' => $partner,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login partner
     */
    public function login(PartnerLoginRequest $request): JsonResponse
    {
        try {
            $partner = Partner::where('email', $request->email)->first();

            if (!$partner || !Hash::check($request->password, $partner->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if (!$partner->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support.',
                ], 403);
            }

            if ($partner->approval_status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been rejected. Reason: ' . ($partner->rejection_reason ?? 'Not specified'),
                ], 403);
            }

            // Revoke all previous tokens
            $partner->tokens()->delete();

            // Create new token
            $token = $partner->createToken('partner-token', ['partner'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'partner' => $partner,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'approval_status' => $partner->approval_status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout partner
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user('sanctum')->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated partner profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $partner = $request->user('sanctum');

            return response()->json([
                'success' => true,
                'data' => [
                    'partner' => $partner,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}


