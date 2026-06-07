<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuotePaymentResource;
use App\Models\Quote;
use App\Services\Payments\Asaas\AsaasApiException;
use App\Services\Payments\QuotePixPaymentService;
use Illuminate\Http\JsonResponse;

class QuotePaymentController extends Controller
{
    public function show(Quote $quote, QuotePixPaymentService $paymentService): JsonResponse
    {
        $this->authorize('view', $quote);

        $payment = $paymentService->findForQuote($quote);

        return response()->json([
            'payment' => $payment ? QuotePaymentResource::make($payment)->resolve() : null,
        ]);
    }

    public function store(Quote $quote, QuotePixPaymentService $paymentService): JsonResponse
    {
        $this->authorize('pay', $quote);

        $hadPayment = $quote->payment !== null;

        try {
            $payment = $paymentService->createPixPayment($quote, request()->user());
        } catch (AsaasApiException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'payment' => QuotePaymentResource::make($payment)->resolve(),
        ], $hadPayment ? 200 : 201);
    }
}
