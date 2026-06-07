import {
  isLaraThemeColor,
  type LaraThemeColor,
  type ThemeMode,
} from "@/lib/primeThemes";
import {
  DEFAULT_THEME_COLOR,
  DEFAULT_THEME_MODE,
  THEME_STORAGE_KEY,
} from "@/lib/themeInit";

export const THEME_COOKIE_NAME = "prime-theme";

export function serializeThemeCookie(
  theme: ThemeMode,
  color: LaraThemeColor,
): string {
  return `${theme}.${color}`;
}

export function parseThemeCookie(
  value: string | undefined,
): { theme: ThemeMode; color: LaraThemeColor } | null {
  if (!value) {
    return null;
  }

  const [mode, color] = value.split(".");
  if (mode !== "light" && mode !== "dark") {
    return null;
  }

  if (!color || !isLaraThemeColor(color)) {
    return null;
  }

  return { theme: mode, color };
}

export function resolveTheme(cookieValue?: string): {
  theme: ThemeMode;
  color: LaraThemeColor;
} {
  return (
    parseThemeCookie(cookieValue) ?? {
      theme: DEFAULT_THEME_MODE,
      color: DEFAULT_THEME_COLOR,
    }
  );
}

export function writeThemeCookie(theme: ThemeMode, color: LaraThemeColor): void {
  if (typeof document === "undefined") {
    return;
  }

  const value = serializeThemeCookie(theme, color);
  document.cookie = `${THEME_COOKIE_NAME}=${encodeURIComponent(value)};path=/;max-age=31536000;SameSite=Lax`;
}

export function persistThemeCookieFromStorage(persistedJson: string): void {
  try {
    const parsed = JSON.parse(persistedJson) as {
      state?: { theme?: string; color?: string };
    };
    const state = (parsed.state ?? parsed) as { theme?: string; color?: string };
    const theme: ThemeMode = state.theme === "dark" ? "dark" : DEFAULT_THEME_MODE;
    const color =
      state.color && isLaraThemeColor(state.color)
        ? state.color
        : DEFAULT_THEME_COLOR;

    writeThemeCookie(theme, color);
  } catch {
    // Ignore malformed persisted state.
  }
}

export function readPersistedThemeFromStorage(): {
  theme: ThemeMode;
  color: LaraThemeColor;
} {
  if (typeof window === "undefined") {
    return { theme: DEFAULT_THEME_MODE, color: DEFAULT_THEME_COLOR };
  }

  try {
    const raw = window.localStorage.getItem(THEME_STORAGE_KEY);
    if (!raw) {
      return { theme: DEFAULT_THEME_MODE, color: DEFAULT_THEME_COLOR };
    }

    const parsed = JSON.parse(raw) as { state?: { theme?: string; color?: string } };
    const state = (parsed.state ?? parsed) as { theme?: string; color?: string };
    const theme: ThemeMode = state.theme === "dark" ? "dark" : DEFAULT_THEME_MODE;
    const color =
      state.color && isLaraThemeColor(state.color)
        ? state.color
        : DEFAULT_THEME_COLOR;

    return { theme, color };
  } catch {
    return { theme: DEFAULT_THEME_MODE, color: DEFAULT_THEME_COLOR };
  }
}
