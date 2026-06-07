"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Card } from "primereact/card";
import { Message } from "primereact/message";
import { Tag } from "primereact/tag";
import { useFormat } from "@/lib/hooks/useFormat";
import { extractApiErrorMessage, fetchQuotes } from "../services/quoteApi";
import type { QuoteListItem } from "../types/quote";

export function QuoteHistory({ refreshKey = 0 }: { refreshKey?: number }) {
  const t = useTranslations("history");
  const { formatCurrency, formatDate } = useFormat();
  const [quotes, setQuotes] = useState<QuoteListItem[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchQuotes()
      .then((result) => setQuotes(result.data))
      .catch((loadError) =>
        setError(extractApiErrorMessage(loadError) || t("loadError")),
      );
  }, [refreshKey, t]);

  return (
    <Card title={t("title")} className="shadow-md">
      {error ? (
        <Message severity="error" text={error} className="mb-3 w-full" />
      ) : null}

      {quotes.length === 0 ? (
        <p className="m-0 text-slate-500 dark:text-zinc-400">{t("empty")}</p>
      ) : (
        <div className="space-y-4">
          {quotes.map((quote) => (
            <div
              key={quote.id}
              className="rounded-lg border border-slate-200 p-3 dark:border-zinc-800"
            >
              <div className="mb-2 flex items-center justify-between gap-2">
                <Tag value={quote.destination ?? "-"} />
                <span className="font-semibold">
                  {formatCurrency(quote.final_total)}
                </span>
              </div>
              <p className="m-0 text-sm text-slate-500 dark:text-zinc-400">
                {formatDate(quote.start_date)} → {formatDate(quote.end_date)} ·{" "}
                {quote.charged_days} days
              </p>
            </div>
          ))}
        </div>
      )}
    </Card>
  );
}
