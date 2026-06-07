import { FilterMatchMode, FilterOperator } from "../types";

export function createTextColumnFilter() {
  return {
    operator: FilterOperator.AND,
    constraints: [
      { value: null as string | null, matchMode: FilterMatchMode.CONTAINS },
    ],
  };
}
