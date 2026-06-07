import { InputText } from "primereact/inputtext";
import type { ColumnFilterOptions } from "../types";

interface TextFilterInputProps {
  options: ColumnFilterOptions;
  placeholder?: string;
}

export function TextFilterInput({ options, placeholder }: TextFilterInputProps) {
  return (
    <InputText
      value={(options.value as string | null) ?? ""}
      onChange={(event) => options.filterCallback(event.target.value, options.index)}
      placeholder={placeholder}
      className="p-column-filter w-full"
    />
  );
}
