import type { DataTableFiltersState } from "./types";

function normalizeFilterValue(value: unknown): unknown {
  if (value instanceof Date) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, "0");
    const day = String(value.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
  }

  if (Array.isArray(value)) {
    return value.map(normalizeFilterValue);
  }

  if (value && typeof value === "object") {
    return Object.fromEntries(
      Object.entries(value).map(([key, nested]) => [key, normalizeFilterValue(nested)]),
    );
  }

  return value;
}

export function serializeDataTableFilters(
  filters: DataTableFiltersState | null,
): DataTableFiltersState {
  if (!filters) {
    return {};
  }

  return normalizeFilterValue(filters) as DataTableFiltersState;
}

export function isDataTableFiltersEmpty(
  filters: DataTableFiltersState | null,
): boolean {
  if (!filters) {
    return true;
  }

  return Object.entries(filters).every(([, meta]) => {
    if (!meta || typeof meta !== "object") {
      return true;
    }

    if ("value" in meta) {
      const value = meta.value;
      return value === null || value === undefined || value === "";
    }

    if ("constraints" in meta && Array.isArray(meta.constraints)) {
      return meta.constraints.every((constraint) => {
        const value = constraint?.value;
        return value === null || value === undefined || value === "";
      });
    }

    return true;
  });
}
