"use client";

import { useCallback, useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { InputText } from "primereact/inputtext";
import { Message } from "primereact/message";
import { Tag } from "primereact/tag";
import { useFormat } from "@/lib/hooks/useFormat";
import {
  createQuotePayment,
  fetchQuotePayment,
  resolvePixPaymentErrorMessage,
} from "../services/paymentApi";
import { usePixExpirationCountdown } from "../hooks/usePixExpirationCountdown";
import type { QuotePayment } from "../types/payment";

const POLL_INTERVAL_MS = 5000;

interface QuotePixPaymentProps {
  quoteId?: number;
  initialPayment?: QuotePayment | null;
}

export function QuotePixPayment({
  quoteId,
  initialPayment = null,
}: QuotePixPaymentProps) {
  const t = useTranslations("summary.payment");
  const { formatCurrency } = useFormat();
  const [payment, setPayment] = useState<QuotePayment | null>(initialPayment);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);
  const expirationCountdown = usePixExpirationCountdown(
    payment?.status === "pending" ? payment.pix_expiration_date : null,
  );

  const isPixExpired =
    payment?.status === "overdue" || expirationCountdown?.expired === true;

  const isPixActive =
    payment?.status === "pending" &&
    !isPixExpired &&
    Boolean(payment.pix_encoded_image);

  const loadPayment = useCallback(async () => {
    if (!quoteId) {
      return;
    }

    try {
      const response = await fetchQuotePayment(quoteId);
      setPayment(response.payment);
      setError(null);
    } catch (loadError) {
      setError(
        resolvePixPaymentErrorMessage(loadError, {
          configuration: t("configurationError"),
          fallback: t("loadError"),
        }),
      );
    }
  }, [quoteId, t]);

  useEffect(() => {
    setPayment(initialPayment ?? null);
  }, [initialPayment]);

  useEffect(() => {
    if (!quoteId) {
      return;
    }

    void loadPayment();
  }, [quoteId, loadPayment]);

  useEffect(() => {
    if (!quoteId || payment?.status !== "pending") {
      return;
    }

    const intervalId = window.setInterval(() => {
      void loadPayment();
    }, POLL_INTERVAL_MS);

    return () => window.clearInterval(intervalId);
  }, [quoteId, payment?.status, loadPayment]);

  const handleGeneratePix = async () => {
    if (!quoteId) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await createQuotePayment(quoteId);
      setPayment(response.payment);
    } catch (createError) {
      setError(
        resolvePixPaymentErrorMessage(createError, {
          configuration: t("configurationError"),
          fallback: t("createError"),
        }),
      );
    } finally {
      setLoading(false);
    }
  };

  const handleCopyPixCode = async () => {
    if (!payment?.pix_payload) {
      return;
    }

    await navigator.clipboard.writeText(payment.pix_payload);
    setCopied(true);
    window.setTimeout(() => setCopied(false), 2000);
  };

  if (!quoteId) {
    return (
      <p className="mt-4 text-sm text-slate-500 dark:text-zinc-400">
        {t("saveQuoteFirst")}
      </p>
    );
  }

  const statusSeverity = {
    pending: "warning",
    paid: "success",
    overdue: "danger",
    cancelled: "secondary",
  } as const;

  return (
    <div className="mt-4 space-y-3 rounded-lg border border-slate-200 p-4 dark:border-zinc-800">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <h3 className="m-0 text-base font-semibold">{t("title")}</h3>
        {payment ? (
          <Tag
            value={t(`status.${payment.status}`)}
            severity={statusSeverity[payment.status]}
          />
        ) : null}
      </div>

      {error ? <Message severity="error" text={error} className="w-full" /> : null}

      {payment?.environment === "sandbox" ? (
        <Message severity="info" text={t("sandboxNotice")} className="w-full" />
      ) : null}

      {payment &&
      payment.quote_total !== undefined &&
      payment.value < payment.quote_total ? (
        <Message
          severity="info"
          text={t("testChargeNotice", {
            chargeAmount: formatCurrency(payment.value),
            quoteTotal: formatCurrency(payment.quote_total),
            percentage: payment.charge_percentage ?? 0.1,
          })}
          className="w-full"
        />
      ) : null}

      {payment?.status === "paid" ? (
        <Message severity="success" text={t("paidMessage")} className="w-full" />
      ) : null}

      {isPixActive ? (
        <div className="flex flex-col items-center gap-3">
          <img
            src={`data:image/png;base64,${payment.pix_encoded_image}`}
            alt={t("qrAlt")}
            className="h-48 w-48 rounded-md border border-slate-200 bg-white p-2 dark:border-zinc-700"
          />
          {payment.pix_payload ? (
            <div className="flex w-full flex-col gap-2">
              <label htmlFor="pix-copy-paste" className="text-sm font-medium">
                {t("copyPasteLabel")}
              </label>
              <div className="flex gap-2">
                <InputText
                  id="pix-copy-paste"
                  value={payment.pix_payload}
                  readOnly
                  className="w-full text-xs"
                />
                <Button
                  type="button"
                  icon={copied ? "pi pi-check" : "pi pi-copy"}
                  label={copied ? t("copied") : t("copy")}
                  severity="secondary"
                  outlined
                  onClick={() => void handleCopyPixCode()}
                />
              </div>
            </div>
          ) : null}
          {expirationCountdown ? (
            <p className="m-0 text-xs text-slate-500 dark:text-zinc-400">
              {expirationCountdown.expired
                ? t("expired")
                : `${t("expiresIn")}: ${expirationCountdown.label}`}
            </p>
          ) : null}
        </div>
      ) : null}

      {isPixExpired && payment?.status !== "paid" ? (
        <>
          <Message severity="warn" text={t("expired")} className="w-full" />
          <Button
            type="button"
            label={loading ? t("generating") : t("refreshPixExpired")}
            icon={loading ? "pi pi-spin pi-spinner" : "pi pi-refresh"}
            onClick={() => void handleGeneratePix()}
            disabled={loading}
            className="w-full"
          />
        </>
      ) : null}

      {!payment ? (
        <Button
          type="button"
          label={loading ? t("generating") : t("generatePix")}
          icon={loading ? "pi pi-spin pi-spinner" : "pi pi-qrcode"}
          onClick={() => void handleGeneratePix()}
          disabled={loading}
          className="w-full"
        />
      ) : null}

      {payment?.status === "pending" && !payment.pix_encoded_image ? (
        <Button
          type="button"
          label={loading ? t("generating") : t("refreshPix")}
          icon={loading ? "pi pi-spin pi-spinner" : "pi pi-refresh"}
          severity="secondary"
          outlined
          onClick={() => void handleGeneratePix()}
          disabled={loading}
          className="w-full"
        />
      ) : null}
    </div>
  );
}
