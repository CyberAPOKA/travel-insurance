"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { useRouter } from "@/i18n/navigation";
import { AuthCard } from "@/features/auth/components/AuthCard";
import {
  rehydrateAuthStore,
  useAuthStore,
} from "@/features/auth/store/authStore";
import { QuoteForm } from "./QuoteForm";

export function NewQuotePageClient() {
  const t = useTranslations("quotesList");
  const router = useRouter();
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
    return null;
  }

  if (!token) {
    return <AuthCard />;
  }

  return (
    <div>
      <div className="mb-4">
        <Button
          size="small"
          type="button"
          label={t("backToList")}
          icon="pi pi-arrow-left"
          severity="secondary"
          text
          onClick={() => router.push("/")}
        />
      </div>
      <QuoteForm />
    </div>
  );
}
