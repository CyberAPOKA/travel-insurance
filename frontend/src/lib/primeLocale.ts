import { addLocale, locale as setPrimeLocale } from "primereact/api";
import type { LocaleOptions } from "primereact/api";
import enPrimeLocale from "@/lib/locales/primereact-en.json";
import ptPrimeLocale from "@/lib/locales/primereact-pt.json";
import { normalizeAppLocale, type AppLocale } from "@/lib/format";

let isRegistered = false;

export function registerPrimeLocales(): void {
  if (isRegistered) {
    return;
  }

  addLocale("en", enPrimeLocale.en as LocaleOptions);
  addLocale("pt", ptPrimeLocale.pt as LocaleOptions);
  isRegistered = true;
}

export function applyPrimeLocale(appLocale: string): AppLocale {
  registerPrimeLocales();
  const locale = normalizeAppLocale(appLocale);
  setPrimeLocale(locale);

  return locale;
}
