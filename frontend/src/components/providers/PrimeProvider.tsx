"use client";

import { PrimeReactProvider } from "primereact/api";
import { useLocale } from "next-intl";
import { useEffect, useState } from "react";
import { applyPrimeLocale } from "@/lib/primeLocale";
import { getPrimeThemeStylesheet } from "@/lib/primeThemes";
import {
  readPersistedThemeFromStorage,
  writeThemeCookie,
} from "@/lib/themeCookie";
import { PRIME_THEME_LINK_ID } from "@/lib/themeInit";
import { rehydrateAuthStore } from "@/features/auth/store/authStore";
import { useThemeStore } from "@/features/quotes/store/themeStore";

function applyThemeToDocument(theme: string, color: string): void {
  const link = document.getElementById(PRIME_THEME_LINK_ID) as HTMLLinkElement | null;
  if (link) {
    link.href = getPrimeThemeStylesheet(
      theme as "light" | "dark",
      color as Parameters<typeof getPrimeThemeStylesheet>[1],
    );
  }

  document.documentElement.dataset.theme = theme;
  document.documentElement.dataset.themeColor = color;
  document.documentElement.classList.toggle("dark", theme === "dark");
}

function PrimeThemeLink() {
  const theme = useThemeStore((state) => state.theme);
  const color = useThemeStore((state) => state.color);
  const [themeHydrated, setThemeHydrated] = useState(false);

  useEffect(() => {
    if (useThemeStore.persist.hasHydrated()) {
      setThemeHydrated(true);
    }

    const unsubscribe = useThemeStore.persist.onFinishHydration(() => {
      setThemeHydrated(true);
    });

    useThemeStore.persist.rehydrate();
    void rehydrateAuthStore();

    return unsubscribe;
  }, []);

  useEffect(() => {
    if (!themeHydrated) {
      return;
    }

    const persisted = readPersistedThemeFromStorage();
    writeThemeCookie(persisted.theme, persisted.color);
    applyThemeToDocument(theme, color);
  }, [theme, color, themeHydrated]);

  return null;
}

function PrimeReactConfig({ children }: { children: React.ReactNode }) {
  const appLocale = useLocale();
  const primeLocale = applyPrimeLocale(appLocale);

  useEffect(() => {
    applyPrimeLocale(appLocale);
  }, [appLocale]);

  return (
    <PrimeReactProvider value={{ locale: primeLocale }} key={primeLocale}>
      <PrimeThemeLink />
      {children}
    </PrimeReactProvider>
  );
}

export function PrimeProvider({ children }: { children: React.ReactNode }) {
  return <PrimeReactConfig>{children}</PrimeReactConfig>;
}

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  return <>{children}</>;
}
