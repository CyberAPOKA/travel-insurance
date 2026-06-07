import { apiClient, extractApiErrorMessage } from "@/lib/apiClient";
import type { QuotePaymentResponse } from "../types/payment";

export async function fetchQuotePayment(
  quoteId: number,
): Promise<QuotePaymentResponse> {
  const response = await apiClient.get<QuotePaymentResponse>(
    `/quotes/${quoteId}/payment`,
  );

  return response.data;
}

export async function createQuotePayment(
  quoteId: number,
): Promise<QuotePaymentResponse> {
  const response = await apiClient.post<QuotePaymentResponse>(
    `/quotes/${quoteId}/payment`,
  );

  return response.data;
}

export { extractApiErrorMessage };
