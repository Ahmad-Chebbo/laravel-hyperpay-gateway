<?php

declare(strict_types=1);

namespace AhmadShebbo\LaravelHyperpay\Http\Controllers;

use AhmadShebbo\LaravelHyperpay\DTOs\PaymentRequest;
use AhmadShebbo\LaravelHyperpay\Services\HyperPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditCardController extends Controller
{
    protected HyperPayService $hyperPayService;

    public function __construct(HyperPayService $hyperPayService)
    {
        $this->hyperPayService = $hyperPayService;
    }

    /**
     * Display user's credit cards
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $creditCards = $user->activeCreditCards()->orderBy('is_default', 'desc')->get();

        return view('credit-cards.index', compact('creditCards'));
    }

    /**
     * Show form to add new credit card
     */
    public function create(): View
    {
        return view('credit-cards.create');
    }

    /**
     * Store a new credit card (tokenize and save)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'brand' => 'required|string|in:VISA,MASTER,MADA,APPLEPAY,STCPAY',
            'card_number' => 'required|string',
            'card_holder' => 'required|string',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:'.date('Y'),
            'cvv' => 'required|string|min:3|max:4',
            'currency' => 'nullable|string|size:3',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            $user = $request->user();

            // Create payment request with tokenization
            $paymentRequest = new PaymentRequest(
                amount: $request->amount,
                brand: $request->brand,
                cardNumber: $request->card_number,
                cardHolder: $request->card_holder,
                expiryMonth: $request->expiry_month,
                expiryYear: $request->expiry_year,
                cvv: $request->cvv,
                currency: $request->currency,
                createRegistration: true, // Request tokenization
            );

            // Process payment to get registrationId
            $response = $this->hyperPayService->processPayment($paymentRequest);

            if (! $response->isSuccessful()) {
                return response()->json([
                    'success' => false,
                    'error' => $response->getResultDescription(),
                ], 400);
            }

            // Save credit card from response
            $cardInfo = [
                'last_four_digits' => substr($request->card_number, -4),
                'card_holder_name' => $request->card_holder,
                'expiry_month' => str_pad($request->expiry_month, 2, '0', STR_PAD_LEFT),
                'expiry_year' => $request->expiry_year,
                'is_default' => $request->boolean('is_default'),
            ];

            $creditCard = $user->saveCreditCardFromResponse($response->toArray(), $cardInfo);

            if (! $creditCard) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to save credit card',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Credit card saved successfully',
                'data' => [
                    'credit_card' => [
                        'id' => $creditCard->id,
                        'registration_id' => $creditCard->registration_id,
                        'card_type' => $creditCard->card_type,
                        'masked_number' => $creditCard->masked_card_number,
                        'card_holder' => $creditCard->card_holder_name,
                        'expiry' => $creditCard->formatted_expiry,
                        'is_default' => $creditCard->is_default,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show credit card details
     */
    public function show(Request $request, string $registrationId): View
    {
        $user = $request->user();
        $creditCard = $user->getCreditCardByRegistrationId($registrationId);

        if (! $creditCard) {
            abort(404, 'Credit card not found');
        }

        return view('credit-cards.show', compact('creditCard'));
    }

    /**
     * Update credit card (set as default, etc.)
     */
    public function update(Request $request, string $registrationId): JsonResponse
    {
        $request->validate([
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $creditCard = $user->getCreditCardByRegistrationId($registrationId);

        if (! $creditCard) {
            return response()->json([
                'success' => false,
                'error' => 'Credit card not found',
            ], 404);
        }

        try {
            if ($request->has('is_default') && $request->boolean('is_default')) {
                $creditCard->setAsDefault();
            }

            if ($request->has('is_active')) {
                if ($request->boolean('is_active')) {
                    $creditCard->reactivate();
                } else {
                    $creditCard->deactivate();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Credit card updated successfully',
                'data' => [
                    'credit_card' => [
                        'id' => $creditCard->id,
                        'registration_id' => $creditCard->registration_id,
                        'card_type' => $creditCard->card_type,
                        'masked_number' => $creditCard->masked_card_number,
                        'card_holder' => $creditCard->card_holder_name,
                        'expiry' => $creditCard->formatted_expiry,
                        'is_default' => $creditCard->is_default,
                        'is_active' => $creditCard->is_active,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove credit card
     */
    public function destroy(Request $request, string $registrationId): JsonResponse
    {
        $user = $request->user();
        $creditCard = $user->getCreditCardByRegistrationId($registrationId);

        if (! $creditCard) {
            return response()->json([
                'success' => false,
                'error' => 'Credit card not found',
            ], 404);
        }

        try {
            $creditCard->delete();

            return response()->json([
                'success' => true,
                'message' => 'Credit card removed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set credit card as default
     */
    public function setDefault(Request $request, string $registrationId): JsonResponse
    {
        $user = $request->user();
        $creditCard = $user->getCreditCardByRegistrationId($registrationId);

        if (! $creditCard) {
            return response()->json([
                'success' => false,
                'error' => 'Credit card not found',
            ], 404);
        }

        try {
            $creditCard->setAsDefault();

            return response()->json([
                'success' => true,
                'message' => 'Credit card set as default successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's credit cards (API)
     */
    public function getCards(Request $request): JsonResponse
    {
        $user = $request->user();
        $creditCards = $user->activeCreditCards()
            ->orderBy('is_default', 'desc')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'registration_id' => $card->registration_id,
                    'card_type' => $card->card_type,
                    'masked_number' => $card->masked_card_number,
                    'card_holder' => $card->card_holder_name,
                    'expiry' => $card->formatted_expiry,
                    'is_default' => $card->is_default,
                    'is_expired' => $card->is_expired,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'credit_cards' => $creditCards,
                'total' => $creditCards->count(),
            ],
        ]);
    }

    /**
     * Process payment using saved credit card
     */
    public function payWithCard(Request $request, string $registrationId): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'merchant_transaction_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $creditCard = $user->getCreditCardByRegistrationId($registrationId);

        if (! $creditCard) {
            return response()->json([
                'success' => false,
                'error' => 'Credit card not found',
            ], 404);
        }

        if ($creditCard->is_expired) {
            return response()->json([
                'success' => false,
                'error' => 'Credit card is expired',
            ], 400);
        }

        try {
            $paymentRequest = new PaymentRequest(
                amount: $request->amount,
                brand: $creditCard->card_type,
                currency: $request->currency,
                merchantTransactionId: $request->merchant_transaction_id,
                registrationId: $creditCard->registration_id,
            );

            $response = $this->hyperPayService->processPayment($paymentRequest);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $response->id,
                    'result_code' => $response->getResultCode(),
                    'result_description' => $response->getResultDescription(),
                    'is_successful' => $response->isSuccessful(),
                    'amount' => $response->getAmount(),
                    'currency' => $response->getCurrency(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
