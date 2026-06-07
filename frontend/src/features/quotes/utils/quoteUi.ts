import type { AddOn } from "../types/quote";

export function buildAddOnOptions(
  addOns: readonly AddOn[],
  translate: (key: string) => string,
) {
  return addOns.map((addOn) => ({
    label: translate(`addOns.${addOn}`),
    value: addOn,
  }));
}
