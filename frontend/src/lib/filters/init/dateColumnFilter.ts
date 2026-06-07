import { FilterMatchMode, FilterOperator } from "../types";

export function createDateColumnFilter() {
  return {
    operator: FilterOperator.AND,
    constraints: [
      { value: null as Date | null, matchMode: FilterMatchMode.DATE_IS },
    ],
  };
}
