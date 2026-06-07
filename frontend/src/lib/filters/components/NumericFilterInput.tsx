import { InputNumber } from "primereact/inputnumber";
import type { ColumnFilterOptions } from "../types";

interface NumericFilterInputProps {
  options: ColumnFilterOptions;
  placeholder?: string;
  locale?: string;
  mode?: "decimal" | "currency";
  currency?: string;
  suffix?: string;
  min?: number;
  max?: number;
}

export function NumericFilterInput({
  options,
  placeholder,
  locale,
  mode,
  currency,
  suffix,
  min,
  max,
}: NumericFilterInputProps) {
  return (
    <InputNumber
      value={options.value as number | null}
      onChange={(event) => options.filterCallback(event.value ?? null, options.index)}
      placeholder={placeholder}
      locale={locale}
      mode={mode}
      currency={currency}
      suffix={suffix}
      min={min}
      max={max}
      className="p-column-filter w-full"
      inputClassName="w-full"
    />
  );
}
