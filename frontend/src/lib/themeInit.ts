import {
  getPrimeThemeStylesheet,
  type LaraThemeColor,
  type ThemeMode,
} from "@/lib/primeThemes";

export const THEME_STORAGE_KEY = "travel-insurance-theme";
export const PRIME_THEME_LINK_ID = "prime-react-theme";

export const DEFAULT_THEME_MODE: ThemeMode = "light";
export const DEFAULT_THEME_COLOR: LaraThemeColor = "cyan";

export const DEFAULT_PRIME_THEME_HREF = getPrimeThemeStylesheet(
  DEFAULT_THEME_MODE,
  DEFAULT_THEME_COLOR,
);

export function getPrimeThemeHref(theme: ThemeMode, color: LaraThemeColor): string {
  return getPrimeThemeStylesheet(theme, color);
}
