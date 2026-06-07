import type { routing } from "@/i18n/routing";

export type AppLocale = (typeof routing.locales)[number];

const DEFAULT_CURRENCY = "BRL";

const DATE_DISPLAY: Record<AppLocale, Intl.DateTimeFormatOptions> = {
  en: { year: "numeric", month: "short", day: "numeric" },
  pt: { year: "numeric", month: "short", day: "numeric" },
};

const CALENDAR_DATE_FORMAT: Record<AppLocale, string> = {
  en: "mm/dd/yy",
  pt: "dd/mm/yy",
};

export function normalizeAppLocale(locale: string): AppLocale {
  return locale === "pt" ? "pt" : "en";
}

export function resolveIntlLocale(locale: string): string {
  return normalizeAppLocale(locale) === "pt" ? "pt-BR" : "en-US";
}

export function getCalendarDateFormat(locale: string): string {
  return CALENDAR_DATE_FORMAT[normalizeAppLocale(locale)];
}

/** Returns today at local midnight — useful as Calendar minDate. */
export function startOfToday(): Date {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  return today;
}

/** True when `value` is strictly before `reference` (day precision). */
export function isDateBefore(
  value: string | Date | null | undefined,
  reference: string | Date | null | undefined,
): boolean {
  const left = value instanceof Date ? toApiDate(value) : value;
  const right = reference instanceof Date ? toApiDate(reference) : reference;

  if (!left || !right || left === "-" || right === "-") {
    return false;
  }

  return left < right;
}

/** Parses an API date string (YYYY-MM-DD) into a local Date. */
export function parseApiDate(value: string | null | undefined): Date | null {
  if (!value || value === "-") {
    return null;
  }

  const [year, month, day] = value.split("-").map(Number);

  if (!year || !month || !day) {
    return null;
  }

  return new Date(year, month - 1, day);
}

/** Serializes a Date to API format (YYYY-MM-DD). */
export function toApiDate(value: Date | null | undefined): string {
  if (!value) {
    return "";
  }

  const year = value.getFullYear();
  const month = String(value.getMonth() + 1).padStart(2, "0");
  const day = String(value.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

export function formatDisplayDate(
  value: string | Date | null | undefined,
  locale: string,
  options?: Intl.DateTimeFormatOptions,
): string {
  if (!value || value === "-") {
    return "-";
  }

  const date = value instanceof Date ? value : parseApiDate(value);

  if (!date) {
    return String(value);
  }

  return new Intl.DateTimeFormat(resolveIntlLocale(locale), options ?? DATE_DISPLAY[normalizeAppLocale(locale)]).format(
    date,
  );
}

export function formatCurrency(
  value: number,
  locale: string,
  currency: string = DEFAULT_CURRENCY,
): string {
  return new Intl.NumberFormat(resolveIntlLocale(locale), {
    style: "currency",
    currency,
  }).format(value);
}

export function formatNumber(
  value: number,
  locale: string,
  options?: Intl.NumberFormatOptions,
): string {
  return new Intl.NumberFormat(resolveIntlLocale(locale), options).format(value);
}

export function formatPercent(value: number, locale: string): string {
  return new Intl.NumberFormat(resolveIntlLocale(locale), {
    style: "percent",
    maximumFractionDigits: 0,
  }).format(value / 100);
}
