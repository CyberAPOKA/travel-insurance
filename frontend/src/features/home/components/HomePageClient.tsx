"use client";

import { useEffect, useState } from "react";
import { useAuthStore, rehydrateAuthStore } from "@/features/auth/store/authStore";
import { AuthCard } from "@/features/auth/components/AuthCard";
import { QuotesTable } from "@/features/quotes/components/QuotesTable";

export function HomePageClient() {
  const token = useAuthStore((state) => state.token);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    let isMounted = true;

    void rehydrateAuthStore().finally(() => {
      if (isMounted) {
        setIsReady(true);
      }
    });

    return () => {
      isMounted = false;
    };
  }, []);

  if (!isReady) {
    return <div className="min-h-[70vh]" aria-hidden />;
  }

  if (!token) {
    return <AuthCard />;
  }

  return <QuotesTable />;
}
