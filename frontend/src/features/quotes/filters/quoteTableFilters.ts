import {
  createDateColumnFilter,
  createDropdownColumnFilter,
  createGlobalFilter,
  createNumericColumnFilter,
} from "@/lib/filters/init";
import type { DataTableFiltersState } from "@/lib/filters/types";

export function createQuoteTableFilters(): DataTableFiltersState {
  return {
    global: createGlobalFilter(),
    destination: createDropdownColumnFilter(),
    start_date: createDateColumnFilter(),
    end_date: createDateColumnFilter(),
    charged_days: createNumericColumnFilter(),
    travelers_count: createNumericColumnFilter(),
    group_discount_percentage: createNumericColumnFilter(),
    final_total: createNumericColumnFilter(),
  };
}

export const QUOTE_TABLE_GLOBAL_FILTER_FIELDS = [
  "destination",
  "start_date",
  "end_date",
];
