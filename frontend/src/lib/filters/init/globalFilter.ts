import { FilterMatchMode } from "../types";

export function createGlobalFilter() {
  return {
    value: null as string | null,
    matchMode: FilterMatchMode.CONTAINS,
  };
}
