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

const ASAAS_CONFIGURATION_ERROR_PATTERNS = [
  /access_token/i,
  /autentica/i,
  /not configured/i,
  /não está configurado/i,
];

export function resolvePixPaymentErrorMessage(
  error: unknown,
  messages: { configuration: string; fallback: string },
): string {
  const raw = extractApiErrorMessage(error);

  if (
    ASAAS_CONFIGURATION_ERROR_PATTERNS.some((pattern) => pattern.test(raw))
  ) {
    return messages.configuration;
  }

  return raw || messages.fallback;
}

export { extractApiErrorMessage };
