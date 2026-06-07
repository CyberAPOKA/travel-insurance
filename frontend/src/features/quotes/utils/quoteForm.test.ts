import { describe, expect, it } from "vitest";
import {
  resolveQuoteFromSubmitResponse,
  mapQuoteToFormValues,
} from "./quoteForm";
import type { QuoteResponse } from "../types/quote";

describe("resolveQuoteFromSubmitResponse", () => {
  it("reads a quote from an axios-like response wrapper", () => {
    const quote = {
      id: 10,
      final_total: 852.5,
      charged_days: 11,
      group_discount_percentage: 0,
    };

    expect(resolveQuoteFromSubmitResponse({ data: quote })).toEqual(quote);
  });

  it("reads a quote returned directly by the API", () => {
    const quote = {
      id: 11,
      final_total: 50,
      charged_days: 5,
      group_discount_percentage: 0,
    };

    expect(resolveQuoteFromSubmitResponse(quote)).toEqual(quote);
  });

  it("returns null for invalid payloads", () => {
    expect(resolveQuoteFromSubmitResponse(null)).toBeNull();
    expect(resolveQuoteFromSubmitResponse({ data: { id: 1 } })).toBeNull();
  });
});

describe("mapQuoteToFormValues", () => {
  it("maps persisted quote data into form defaults", () => {
    const quote: QuoteResponse = {
      id: 1,
      destination: "EUROPE",
      start_date: "2026-07-10",
      end_date: "2026-07-20",
      charged_days: 11,
      group_discount_percentage: 0,
      final_total: 852.5,
      travelers: [
        {
          name: "Ana",
          birth_date: "1990-03-15",
          age: 36,
          subtotal: 335.5,
          applied_add_ons: ["LUGGAGE", "ADVENTURE_SPORTS"],
        },
      ],
    };

    expect(mapQuoteToFormValues(quote)).toEqual({
      destination: "EUROPE",
      start_date: "2026-07-10",
      end_date: "2026-07-20",
      travelers: [
        {
          name: "Ana",
          birth_date: "1990-03-15",
          add_ons: ["LUGGAGE", "ADVENTURE_SPORTS"],
        },
      ],
    });
  });
});
