import { routing } from "@/i18n/routing";

export type AppLocale = (typeof routing.locales)[number];

export function resolveAppLocaleFromPath(pathname?: string): AppLocale {
  const path = pathname ?? (typeof window !== "undefined" ? window.location.pathname : "");
  const segment = path.split("/").filter(Boolean)[0];

  return routing.locales.includes(segment as AppLocale)
    ? (segment as AppLocale)
    : routing.defaultLocale;
}

export function localeAcceptLanguage(locale: string): string {
  return locale === "pt" ? "pt-BR,pt;q=0.9" : "en-US,en;q=0.9";
}

export function localeRequestHeaders(locale?: string): Record<string, string> {
  const resolved = locale ?? resolveAppLocaleFromPath();

  return {
    "Accept-Language": localeAcceptLanguage(resolved),
    "X-App-Locale": resolved,
  };
}
