<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\Asaas\AsaasWebhookProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, AsaasWebhookProcessor $processor): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->all();

        $processor->handle($payload);

        return response()->json(['received' => true]);
    }
}
