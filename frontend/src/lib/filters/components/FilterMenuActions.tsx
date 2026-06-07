import { Button } from "primereact/button";
import type {
  ColumnFilterApplyTemplateOptions,
  ColumnFilterClearTemplateOptions,
} from "primereact/column";

export function FilterClearButton(options: ColumnFilterClearTemplateOptions) {
  return (
    <Button
      type="button"
      icon="pi pi-times"
      severity="secondary"
      outlined
      onClick={options.filterClearCallback}
      aria-label="Clear filter"
    />
  );
}

export function FilterApplyButton(options: ColumnFilterApplyTemplateOptions) {
  return (
    <Button
      type="button"
      icon="pi pi-check"
      severity="success"
      onClick={options.filterApplyCallback}
      aria-label="Apply filter"
    />
  );
}
