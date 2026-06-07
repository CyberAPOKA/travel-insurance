import type { AddOn, DestinationZone, QuoteResponse } from "../types/quote";

export interface QuoteFormValues extends Record<string, unknown> {
  destination: DestinationZone | null;
  start_date: string;
  end_date: string;
  travelers: {
    name: string;
    birth_date: string;
    add_ons: AddOn[];
  }[];
}

export function mapQuoteToFormValues(quote: QuoteResponse): QuoteFormValues {
  return {
    destination: (quote.destination as DestinationZone | null) ?? null,
    start_date: quote.start_date ?? "",
    end_date: quote.end_date ?? "",
    travelers: (quote.travelers ?? []).map((traveler, index) => ({
      name: traveler.name,
      birth_date:
        traveler.birth_date ??
        quote.calculation_breakdown?.travelers[index]?.birth_date ??
        "",
      add_ons: traveler.add_ons?.length
        ? traveler.add_ons
        : traveler.applied_add_ons ?? [],
    })),
  };
}

export function resolveQuoteFromSubmitResponse(response: unknown): QuoteResponse | null {
  if (!response || typeof response !== "object") {
    return null;
  }

  const candidate =
    "data" in response &&
    (response as { data?: unknown }).data &&
    typeof (response as { data?: unknown }).data === "object"
      ? (response as { data: unknown }).data
      : response;

  if (!candidate || typeof candidate !== "object") {
    return null;
  }

  if ("final_total" in candidate) {
    return candidate as QuoteResponse;
  }

  if (
    "data" in candidate &&
    candidate.data &&
    typeof candidate.data === "object" &&
    "final_total" in candidate.data
  ) {
    return candidate.data as QuoteResponse;
  }

  return null;
}
