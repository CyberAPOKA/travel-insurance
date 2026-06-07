"use client";

import { create } from "zustand";
import { persist, createJSONStorage } from "zustand/middleware";
import {
  isLaraThemeColor,
  type LaraThemeColor,
  type ThemeMode,
} from "@/lib/primeThemes";
import { persistThemeCookieFromStorage } from "@/lib/themeCookie";
import { THEME_STORAGE_KEY } from "@/lib/themeInit";

interface ThemeStore {
  theme: ThemeMode;
  color: LaraThemeColor;
  setTheme: (theme: ThemeMode) => void;
  setColor: (color: LaraThemeColor) => void;
  toggleTheme: () => void;
}

const themeLocalStorage = createJSONStorage<ThemeStore>(() => ({
  getItem: (name) => localStorage.getItem(name),
  setItem: (name, value) => {
    localStorage.setItem(name, value);
    persistThemeCookieFromStorage(value);
  },
  removeItem: (name) => localStorage.removeItem(name),
}));

export const useThemeStore = create<ThemeStore>()(
  persist(
    (set, get) => ({
      theme: "light",
      color: "cyan",
      setTheme: (theme) => set({ theme }),
      setColor: (color) => set({ color }),
      toggleTheme: () =>
        set({ theme: get().theme === "light" ? "dark" : "light" }),
    }),
    {
      name: THEME_STORAGE_KEY,
      storage: themeLocalStorage,
      skipHydration: true,
      merge: (persistedState, currentState) => {
        const persisted = persistedState as Partial<ThemeStore> | undefined;

        return {
          ...currentState,
          ...persisted,
          color:
            persisted?.color && isLaraThemeColor(persisted.color)
              ? persisted.color
              : currentState.color,
          theme: persisted?.theme === "dark" ? "dark" : "light",
        };
      },
    },
  ),
);

export type { ThemeMode as Theme };
