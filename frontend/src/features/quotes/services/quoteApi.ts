import { apiClient, extractApiErrorMessage } from "@/lib/apiClient";
import {
  isDataTableFiltersEmpty,
  serializeDataTableFilters,
  type DataTableFiltersState,
} from "@/lib/filters";
import type { QuoteListItem, QuoteRequestPayload, QuoteResponse } from "../types/quote";

export interface PaginatedQuotes {
  data: QuoteListItem[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    source: "database" | "cache";
  };
}

export interface QuoteListQueryOptions {
  page?: number;
  per_page?: number;
}

export async function createQuote(
  payload: QuoteRequestPayload,
): Promise<QuoteResponse> {
  const response = await apiClient.post<QuoteResponse>("/quotes", payload);
  return response.data;
}

export async function fetchQuotes(
  filters: DataTableFiltersState | null = null,
  pagination: QuoteListQueryOptions = {},
): Promise<PaginatedQuotes> {
  const serialized = serializeDataTableFilters(filters);
  const params: Record<string, string | number> = {
    page: pagination.page ?? 1,
    per_page: pagination.per_page ?? 15,
  };

  if (!isDataTableFiltersEmpty(serialized)) {
    params.filters = JSON.stringify(serialized);
  }

  const response = await apiClient.get<PaginatedQuotes>("/quotes", { params });

  return response.data;
}

export async function fetchQuote(id: number): Promise<QuoteResponse> {
  const response = await apiClient.get<QuoteResponse>(`/quotes/${id}`);
  return response.data;
}

export { extractApiErrorMessage };
