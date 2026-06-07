"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Card } from "primereact/card";
import { Divider } from "primereact/divider";
import { Message } from "primereact/message";
import { Tag } from "primereact/tag";
import { useFormat } from "@/lib/hooks/useFormat";
import type { QuoteResponse } from "../types/quote";
import { quoteWarningKey, translateQuoteWarning } from "../utils/quoteWarnings";
import { QuoteCalculationModal } from "./QuoteCalculationModal";

interface QuoteSummaryProps {
  quote: QuoteResponse | null;
}

export function QuoteSummary({ quote }: QuoteSummaryProps) {
  const t = useTranslations();
  const { formatCurrency } = useFormat();
  const [calculationVisible, setCalculationVisible] = useState(false);

  if (!quote) {
    return (
      <Card title={t("summary.title")} className="shadow-md">
        <p className="text-slate-500 dark:text-zinc-400">
          {t("summary.empty")}
        </p>
      </Card>
    );
  }

  const header = (
    <div className="flex flex-wrap items-center justify-between gap-2 pt-4 mx-4">
      <span className="font-semibold">{t("summary.title")}</span>
      <Button
        type="button"
        size="small"
        label={t("summary.viewCalculation")}
        icon="pi pi-calculator"
        severity="secondary"
        outlined
        onClick={() => setCalculationVisible(true)}
      />
    </div>
  );

  return (
    <>
      <Card header={header} className="shadow-md">
        <div className="mb-4 flex items-center gap-2">
          <Tag
            value={`${t("summary.chargedDays")}: ${quote.charged_days}`}
            severity="info"
          />
        </div>

        <div className="space-y-2">
          {(quote.travelers ?? []).map((traveler) => (
            <div
              key={traveler.name}
              className="bg-slate-50 dark:bg-zinc-900 p-2 rounded-lg border border-slate-200 dark:border-zinc-800"
            >
              <div className="flex items-start justify-between gap-3">
                <div className="space-y-1">
                  <p className="font-semibold">{traveler.name}</p>
                  <p className="text-sm text-slate-500 dark:text-zinc-400">
                    {t("summary.age")}: {traveler.age}
                  </p>
                </div>
                <span className="font-bold">
                  {formatCurrency(traveler.subtotal)}
                </span>
              </div>
              <p className="mt-2 text-sm text-slate-500 dark:text-zinc-400">
                {t("summary.appliedAddOns")}:{" "}
                {traveler.applied_add_ons.length > 0
                  ? traveler.applied_add_ons
                      .map((addOn) => t(`addOns.${addOn}`))
                      .join(", ")
                  : t("summary.none")}
              </p>
            </div>
          ))}
        </div>

        {(quote.warnings?.length ?? 0) > 0 ? (
          <div className="mt-4 space-y-2">
            <p className="font-medium">{t("summary.warnings")}</p>
            {quote.warnings?.map((warning, index) => (
              <div className="space-y-1" key={quoteWarningKey(warning, index)}>
                <Message
                  severity="warn"
                  text={translateQuoteWarning(warning, t)}
                  className="w-full"
                />
              </div>
            ))}
          </div>
        ) : null}

        <Divider />

        <div className="mb-2 flex justify-between">
          <span>{t("summary.groupDiscount")}</span>
          <span>{quote.group_discount_percentage}%</span>
        </div>
        <div className="flex justify-between text-xl font-bold">
          <span>{t("summary.finalTotal")}</span>
          <span>{formatCurrency(quote.final_total)}</span>
        </div>
      </Card>

      <QuoteCalculationModal
        visible={calculationVisible}
        breakdown={quote.calculation_breakdown}
        onHide={() => setCalculationVisible(false)}
      />
    </>
  );
}
