import { Calendar } from "primereact/calendar";
import type { ColumnFilterOptions } from "../types";

interface DateFilterInputProps {
  options: ColumnFilterOptions;
  placeholder?: string;
  dateFormat?: string;
}

export function DateFilterInput({
  options,
  placeholder,
  dateFormat,
}: DateFilterInputProps) {
  return (
    <Calendar
      value={options.value as Date | null}
      onChange={(event) => options.filterCallback(event.value ?? null, options.index)}
      placeholder={placeholder}
      dateFormat={dateFormat}
      showIcon
      showButtonBar
      className="p-column-filter w-full"
      inputClassName="w-full"
    />
  );
}
