import type { ColumnFilterElementTemplateOptions } from "primereact/column";
import { FilterMatchMode, FilterOperator } from "primereact/api";

export type ColumnFilterOptions = ColumnFilterElementTemplateOptions;

export type DataTableFilterMetaData = {
  value: unknown;
  matchMode?: string;
};

export type DataTableOperatorFilterMetaData = {
  operator: string;
  constraints: DataTableFilterMetaData[];
};

export type DataTableFiltersState = Record<
  string,
  DataTableFilterMetaData | DataTableOperatorFilterMetaData
>;

export { FilterMatchMode, FilterOperator };
