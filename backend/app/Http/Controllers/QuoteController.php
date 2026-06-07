<?php

namespace App\Http\Controllers;

use App\DTO\QuoteRequestData;
use App\Http\Requests\IndexQuoteRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Resources\QuoteListResource;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\Quotes\CachedQuotePricingService;
use App\Services\Quotes\QuoteListService;
use App\Services\Quotes\QuotePersistenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuoteController extends Controller
{
    public function __construct(
        private readonly CachedQuotePricingService $quotePricingService,
        private readonly QuotePersistenceService $quotePersistenceService,
    ) {}

    public function index(IndexQuoteRequest $request, QuoteListService $quoteListService): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $result = $quoteListService->paginate(
            userId: $request->user()->id,
            filters: $validated['filters'] ?? [],
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 15),
        );

        return QuoteListResource::collection($result->paginator)
            ->additional([
                'meta' => [
                    'source' => $result->source,
                ],
            ]);
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $quoteRequest = QuoteRequestData::fromArray($request->validated());
        $result = $this->quotePricingService->calculate($quoteRequest);
        $quote = $this->quotePersistenceService->store($request->user(), $quoteRequest, $result);

        return QuoteResource::make($quote->load('travelers'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Quote $quote): QuoteResource
    {
        $this->authorize('view', $quote);

        return QuoteResource::make($quote->load('travelers'));
    }

    public function update(StoreQuoteRequest $request, Quote $quote): QuoteResource
    {
        $this->authorize('update', $quote);

        $quoteRequest = QuoteRequestData::fromArray($request->validated());
        $result = $this->quotePricingService->calculate($quoteRequest);
        $quote = $this->quotePersistenceService->update($quote, $quoteRequest, $result);

        return QuoteResource::make($quote->load('travelers'));
    }
}
