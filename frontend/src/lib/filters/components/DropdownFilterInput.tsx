import { Dropdown, type DropdownChangeEvent } from "primereact/dropdown";
import type { ColumnFilterOptions } from "../types";

interface DropdownFilterOption<T = string> {
  label: string;
  value: T;
}

interface DropdownFilterInputProps<T = string> {
  options: ColumnFilterOptions;
  items: DropdownFilterOption<T>[];
  placeholder?: string;
}

export function DropdownFilterInput<T = string>({
  options,
  items,
  placeholder,
}: DropdownFilterInputProps<T>) {
  return (
    <Dropdown
      value={options.value as T | null}
      options={items}
      onChange={(event: DropdownChangeEvent) =>
        options.filterCallback(event.value ?? null)
      }
      placeholder={placeholder}
      showClear
      className="p-column-filter w-full"
    />
  );
}
