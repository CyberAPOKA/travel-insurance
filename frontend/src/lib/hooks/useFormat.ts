"use client";

import { useLocale } from "next-intl";
import {
  formatCurrency,
  formatDisplayDate,
  formatNumber,
  formatPercent,
  getCalendarDateFormat,
  normalizeAppLocale,
  parseApiDate,
  resolveIntlLocale,
  startOfToday,
  toApiDate,
  isDateBefore,
  type AppLocale,
} from "@/lib/format";

export function useFormat() {
  const locale = useLocale();
  const appLocale = normalizeAppLocale(locale);
  const intlLocale = resolveIntlLocale(locale);
  const calendarDateFormat = getCalendarDateFormat(locale);

  return {
    locale,
    appLocale: appLocale as AppLocale,
    intlLocale,
    calendarDateFormat,
    parseApiDate,
    toApiDate,
    startOfToday,
    isDateBefore,
    formatDate: (value: Parameters<typeof formatDisplayDate>[0], options?: Parameters<typeof formatDisplayDate>[2]) =>
      formatDisplayDate(value, locale, options),
    formatCurrency: (value: number, currency?: string) => formatCurrency(value, locale, currency),
    formatNumber: (value: number, options?: Intl.NumberFormatOptions) => formatNumber(value, locale, options),
    formatPercent: (value: number) => formatPercent(value, locale),
  };
}
