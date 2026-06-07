"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Message } from "primereact/message";
import { ProgressSpinner } from "primereact/progressspinner";
import { useRouter } from "@/i18n/navigation";
import { AuthCard } from "@/features/auth/components/AuthCard";
import {
  rehydrateAuthStore,
  useAuthStore,
} from "@/features/auth/store/authStore";
import { extractApiErrorMessage, fetchQuote } from "../services/quoteApi";
import type { QuoteResponse } from "../types/quote";
import { QuoteForm } from "./QuoteForm";

interface QuoteDetailPageClientProps {
  quoteId: number;
}

export function QuoteDetailPageClient({ quoteId }: QuoteDetailPageClientProps) {
  const t = useTranslations("quotesList");
  const router = useRouter();
  const token = useAuthStore((state) => state.token);
  const clearSession = useAuthStore((state) => state.clearSession);
  const [isReady, setIsReady] = useState(false);
  const [quote, setQuote] = useState<QuoteResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

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

  useEffect(() => {
    if (!isReady || !token) {
      return;
    }

    setLoading(true);
    setError(null);

    fetchQuote(quoteId)
      .then((data) => setQuote(data))
      .catch((loadError) => {
        if (
          typeof loadError === "object" &&
          loadError !== null &&
          "response" in loadError &&
          (loadError as { response?: { status?: number } }).response?.status === 401
        ) {
          clearSession();
          return;
        }

        setError(extractApiErrorMessage(loadError) || t("quoteLoadError"));
      })
      .finally(() => setLoading(false));
  }, [isReady, token, quoteId, t, clearSession]);

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

      {loading ? (
        <div className="flex justify-center py-12">
          <ProgressSpinner style={{ width: "40px", height: "40px" }} />
        </div>
      ) : null}

      {error ? <Message severity="error" text={error} className="mb-4 w-full" /> : null}

      {!loading && !error && quote ? (
        <QuoteForm quoteId={quoteId} initialQuote={quote} />
      ) : null}
    </div>
  );
}
