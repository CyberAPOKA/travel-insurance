"use client";

import { create } from "zustand";
import { persist } from "zustand/middleware";
import { setAuthToken } from "@/lib/apiClient";

export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

interface AuthStore {
  user: AuthUser | null;
  token: string | null;
  setSession: (user: AuthUser, token: string) => void;
  clearSession: () => void;
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      setSession: (user, token) => {
        setAuthToken(token);
        set({ user, token });
      },
      clearSession: () => {
        setAuthToken(null);
        set({ user: null, token: null });
      },
    }),
    {
      name: "travel-insurance-auth",
      skipHydration: true,
      partialize: (state) => ({
        user: state.user,
        token: state.token,
      }),
      onRehydrateStorage: () => (state) => {
        if (state?.token) {
          setAuthToken(state.token);
        }
      },
    },
  ),
);

export function rehydrateAuthStore(): Promise<void> {
  return Promise.resolve(useAuthStore.persist.rehydrate());
}
