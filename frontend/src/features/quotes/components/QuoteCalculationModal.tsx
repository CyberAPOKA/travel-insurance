"use client";

import { useTranslations } from "next-intl";
import { Dialog } from "primereact/dialog";
import { Divider } from "primereact/divider";
import { Tag } from "primereact/tag";
import { useFormat } from "@/lib/hooks/useFormat";
import type { QuoteCalculationBreakdown } from "../types/quote";

interface QuoteCalculationModalProps {
  visible: boolean;
  breakdown: QuoteCalculationBreakdown | null | undefined;
  onHide: () => void;
}

function DetailRow({
  label,
  value,
  formula,
  mono = false,
}: {
  label: string;
  value: React.ReactNode;
  formula?: string | null;
  mono?: boolean;
}) {
  return (
    <div className="grid grid-cols-1 gap-0.5 border-b border-slate-100 py-2 dark:border-zinc-800 sm:grid-cols-2">
      <span className="text-sm text-slate-500 dark:text-zinc-400">{label}</span>
      <div className="text-sm">
        <div className={mono ? "font-mono" : ""}>{value}</div>
        {formula ? (
          <p className="mt-0.5 font-mono text-xs text-slate-400 dark:text-zinc-500">{formula}</p>
        ) : null}
      </div>
    </div>
  );
}

function SectionTitle({ children }: { children: React.ReactNode }) {
  return (
    <h3 className="mb-2 mt-4 font-mono text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">
      {children}
    </h3>
  );
}

export function QuoteCalculationModal({
  visible,
  breakdown,
  onHide,
}: QuoteCalculationModalProps) {
  const t = useTranslations();
  const { formatCurrency, formatDate, formatPercent } = useFormat();

  const formatMoney = (value: number) => formatCurrency(value);
  const formatRate = (value: number) => formatPercent(value * 100);

  return (
    <Dialog
      visible={visible}
      header={t("summary.calculationTitle")}
      modal
      draggable={false}
      className="w-full max-w-3xl"
      onHide={onHide}
    >
      {!breakdown ? (
        <p className="m-0 text-slate-500 dark:text-zinc-400">{t("summary.calculationUnavailable")}</p>
      ) : (
        <div className="max-h-[70vh] overflow-y-auto pr-1 font-mono text-sm">
          <SectionTitle>{t("summary.calculationConstants")}</SectionTitle>
          <div className="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <DetailRow
              label={t("summary.minChargedDays")}
              value={String(breakdown.constants.min_charged_days)}
              mono
            />
            <DetailRow
              label={t("summary.luggageDailyRate")}
              value={formatMoney(breakdown.constants.luggage_daily_rate)}
              mono
            />
            <DetailRow
              label={t("summary.adventureSportsRate")}
              value={formatRate(breakdown.constants.adventure_sports_rate)}
              mono
            />
            <DetailRow
              label={t("summary.groupDiscountThreshold")}
              value={String(breakdown.constants.group_discount_threshold)}
              mono
            />
            <DetailRow
              label={t("summary.groupDiscountRate")}
              value={`${breakdown.constants.group_discount_percentage}%`}
              mono
            />
            <DetailRow
              label={t("summary.adventureSportsAgeRange")}
              value={`${breakdown.constants.adventure_sports_min_age}–${breakdown.constants.adventure_sports_max_age}`}
              mono
            />
          </div>

          <SectionTitle>{t("summary.calculationTrip")}</SectionTitle>
          <div className="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <DetailRow
              label={t("form.destination")}
              value={t(`destinations.${breakdown.trip.destination as "NATIONAL" | "AMERICAS" | "EUROPE"}`)}
            />
            <DetailRow
              label={t("summary.dailyRate")}
              value={formatMoney(breakdown.trip.daily_rate)}
              mono
            />
            <DetailRow
              label={t("form.startDate")}
              value={formatDate(breakdown.trip.start_date)}
            />
            <DetailRow
              label={t("form.endDate")}
              value={formatDate(breakdown.trip.end_date)}
            />
            <DetailRow label={t("summary.tripDays")} value={String(breakdown.trip.trip_days)} mono />
            <DetailRow
              label={t("summary.chargedDays")}
              value={String(breakdown.trip.charged_days)}
              formula={breakdown.trip.charged_days_formula}
              mono
            />
            <DetailRow
              label={t("summary.minDaysApplied")}
              value={breakdown.trip.min_charged_days_applied ? t("summary.yes") : t("summary.no")}
            />
          </div>

          {breakdown.travelers.map((traveler, index) => (
            <div key={`${traveler.name}-${index}`}>
              <SectionTitle>
                {t("summary.calculationTraveler")} {index + 1}: {traveler.name}
              </SectionTitle>
              <div className="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-800 dark:bg-zinc-900">
                <DetailRow label={t("form.birthDate")} value={formatDate(traveler.birth_date)} />
                <DetailRow
                  label={t("summary.ageAtTripStart")}
                  value={String(traveler.age_at_trip_start)}
                  mono
                />
                <DetailRow
                  label={t("summary.ageMultiplier")}
                  value={traveler.age_multiplier_label}
                  mono
                />
                <DetailRow
                  label={t("summary.requestedAddOns")}
                  value={
                    traveler.requested_add_ons.length > 0
                      ? traveler.requested_add_ons.map((addOn) => t(`addOns.${addOn}`)).join(", ")
                      : t("summary.none")
                  }
                />
                <DetailRow
                  label={t("summary.baseAmount")}
                  value={formatMoney(traveler.base_amount)}
                  formula={traveler.base_formula}
                  mono
                />
                <DetailRow
                  label={t("summary.afterAgeMultiplier")}
                  value={formatMoney(traveler.after_age_multiplier)}
                  formula={traveler.after_age_formula}
                  mono
                />
                <DetailRow
                  label={t("summary.adventureSports")}
                  value={
                    traveler.adventure_sports_requested
                      ? traveler.adventure_sports_eligible
                        ? formatMoney(traveler.adventure_sports_amount)
                        : t("summary.notEligible")
                      : t("summary.none")
                  }
                  formula={traveler.adventure_sports_formula}
                  mono
                />
                <DetailRow
                  label={t("summary.luggage")}
                  value={
                    traveler.luggage_requested
                      ? formatMoney(traveler.luggage_amount)
                      : t("summary.none")
                  }
                  formula={traveler.luggage_formula}
                  mono
                />
                <DetailRow
                  label={t("summary.rawSubtotal")}
                  value={formatMoney(traveler.raw_subtotal)}
                  mono
                />
                <DetailRow
                  label={t("summary.roundedSubtotal")}
                  value={formatMoney(traveler.rounded_subtotal)}
                  mono
                />
                <DetailRow
                  label={t("summary.appliedAddOns")}
                  value={
                    traveler.applied_add_ons.length > 0 ? (
                      <div className="flex flex-wrap gap-1">
                        {traveler.applied_add_ons.map((addOn) => (
                          <Tag key={addOn} value={t(`addOns.${addOn}`)} />
                        ))}
                      </div>
                    ) : (
                      t("summary.none")
                    )
                  }
                />
              </div>
            </div>
          ))}

          <SectionTitle>{t("summary.calculationTotals")}</SectionTitle>
          <div className="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <DetailRow
              label={t("summary.travelersCount")}
              value={String(breakdown.summary.travelers_count)}
              mono
            />
            <DetailRow
              label={t("summary.groupSubtotal")}
              value={formatMoney(breakdown.summary.group_subtotal_before_discount)}
              mono
            />
            <DetailRow
              label={t("summary.groupDiscount")}
              value={`${breakdown.summary.group_discount_percentage}%`}
              mono
            />
            <DetailRow
              label={t("summary.discountAmount")}
              value={formatMoney(breakdown.summary.group_discount_amount)}
              mono
            />
            <DetailRow label={t("summary.rounding")} value={breakdown.summary.rounding} mono />
            <Divider className="my-2" />
            <DetailRow
              label={t("summary.finalTotal")}
              value={formatMoney(breakdown.summary.final_total)}
              mono
            />
          </div>
        </div>
      )}
    </Dialog>
  );
}
