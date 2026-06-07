import { FilterMatchMode, FilterOperator } from "../types";

export function createNumericColumnFilter() {
  return {
    operator: FilterOperator.AND,
    constraints: [
      { value: null as number | null, matchMode: FilterMatchMode.EQUALS },
    ],
  };
}
