import { FilterMatchMode } from "../types";

export function createDropdownColumnFilter<T = string>() {
  return {
    value: null as T | null,
    matchMode: FilterMatchMode.EQUALS,
  };
}
