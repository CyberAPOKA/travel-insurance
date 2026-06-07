"use client";

import { useMemo, useState } from "react";
import { useTranslations } from "next-intl";
import { useForm } from "laravel-precognition-react";
import { Button } from "primereact/button";
import { Calendar } from "primereact/calendar";
import { Card } from "primereact/card";
import { Dropdown } from "primereact/dropdown";
import { FloatLabel } from "primereact/floatlabel";
import { Message } from "primereact/message";
import { ProgressSpinner } from "primereact/progressspinner";
import {
  FieldError,
  asFormLike,
  fieldClassName,
} from "@/components/ui/FieldError";
import { useAuthStore } from "@/features/auth/store/authStore";
import { useRouter } from "@/i18n/navigation";
import { useFormat } from "@/lib/hooks/useFormat";
import { DESTINATION_ZONES } from "@/lib/constants";
import "@/lib/precognition";
import type { QuoteResponse } from "../types/quote";
import {
  mapQuoteToFormValues,
  resolveQuoteFromSubmitResponse,
  type QuoteFormValues,
} from "../utils/quoteForm";
import { QuoteSummary } from "./QuoteSummary";
import { TravelersTable, type TravelersTableProps } from "./TravelersTable";

interface QuoteFormProps {
  quoteId?: number;
  initialQuote?: QuoteResponse;
}

const emptyFormValues: QuoteFormValues = {
  destination: null,
  start_date: "",
  end_date: "",
  travelers: [],
};

export function QuoteForm({ quoteId, initialQuote }: QuoteFormProps = {}) {
  const isEditing = quoteId != null;
  const t = useTranslations();
  const router = useRouter();
  const {
    calendarDateFormat,
    parseApiDate,
    toApiDate,
    startOfToday,
    isDateBefore,
  } = useFormat();
  const clearSession = useAuthStore((state) => state.clearSession);
  const [quote, setQuote] = useState<QuoteResponse | null>(
    initialQuote ?? null,
  );
  const [submitError, setSubmitError] = useState<string | null>(null);

  const formInput = useMemo(
    () => (initialQuote ? mapQuoteToFormValues(initialQuote) : emptyFormValues),
    [initialQuote],
  );

  const form = useForm<QuoteFormValues>(
    isEditing ? "put" : "post",
    isEditing ? `/quotes/${quoteId}` : "/quotes",
    formInput,
  );

  const today = useMemo(() => startOfToday(), [startOfToday]);
  const minEndDate = useMemo(
    () => parseApiDate(form.data.start_date) ?? today,
    [form.data.start_date, parseApiDate, today],
  );

  form.setValidationTimeout(500);

  const destinationOptions = DESTINATION_ZONES.map((zone) => ({
    label: t(`destinations.${zone}`),
    value: zone,
  }));

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSubmitError(null);

    try {
      const response = await form.submit({
        onUnauthorized: () => {
          clearSession();
          router.replace("/");
          return Promise.reject(new Error("Unauthorized"));
        },
        onValidationError: () => Promise.reject(new Error("validation")),
      });

      const payload = resolveQuoteFromSubmitResponse(response);

      if (!payload) {
        setSubmitError(t("errors.generic"));
        return;
      }

      if (!isEditing && payload.id != null) {
        router.replace(`/quotes/${payload.id}`);
        return;
      }

      setQuote(payload);
    } catch (error) {
      if (error instanceof Error && error.message === "validation") {
        return;
      }

      if (error instanceof Error && error.message === "Unauthorized") {
        return;
      }

      setSubmitError(t("errors.generic"));
    }
  };

  const clearSubmitError = () => {
    setSubmitError((current) => (current ? null : current));
  };

  return (
    <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
      <div className="lg:col-span-7">
        <Card
          title={isEditing ? t("form.editTitle") : t("app.title")}
          subTitle={isEditing ? t("form.editSubtitle") : t("app.subtitle")}
          className="shadow-md"
        >
          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <FloatLabel>
                <Dropdown
                  inputId="destination"
                  value={form.data.destination}
                  options={destinationOptions}
                  placeholder={t("form.selectDestination")}
                  onChange={(event) => {
                    clearSubmitError();
                    form.setData("destination", event.value ?? null);
                  }}
                  onBlur={() => form.validate("destination")}
                  className={fieldClassName(asFormLike(form), "destination")}
                />
                <label htmlFor="destination">{t("form.destination")}</label>
              </FloatLabel>
              <FieldError form={asFormLike(form)} name="destination" />
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 mt-2">
              <div>
                <FloatLabel>
                  <Calendar
                    inputId="start-date"
                    value={parseApiDate(form.data.start_date)}
                    minDate={today}
                    onChange={(event) => {
                      clearSubmitError();
                      const value = toApiDate(event.value as Date | null);
                      const shouldClearEndDate = Boolean(
                        value && isDateBefore(form.data.end_date, value),
                      );

                      form.setData("start_date", value);

                      if (shouldClearEndDate) {
                        form.setData("end_date", "");
                      }

                      if (value) {
                        form.validate("start_date");
                        if (!shouldClearEndDate && form.data.end_date) {
                          form.validate("end_date");
                        }
                      }
                    }}
                    dateFormat={calendarDateFormat}
                    showIcon
                    className={fieldClassName(asFormLike(form), "start_date")}
                  />
                  <label htmlFor="start-date">{t("form.startDate")}</label>
                </FloatLabel>
                <FieldError form={asFormLike(form)} name="start_date" />
              </div>
              <div>
                <FloatLabel>
                  <Calendar
                    inputId="end-date"
                    value={parseApiDate(form.data.end_date)}
                    minDate={minEndDate}
                    onChange={(event) => {
                      clearSubmitError();
                      const value = toApiDate(event.value as Date | null);
                      form.setData("end_date", value);
                      if (value) {
                        form.validate("end_date");
                      }
                    }}
                    dateFormat={calendarDateFormat}
                    showIcon
                    className={fieldClassName(asFormLike(form), "end_date")}
                  />
                  <label htmlFor="end-date">{t("form.endDate")}</label>
                </FloatLabel>
                <FieldError form={asFormLike(form)} name="end_date" />
              </div>
            </div>

            <TravelersTable
              form={form as unknown as TravelersTableProps["form"]}
              onChange={clearSubmitError}
            />

            <div className="flex justify-end gap-2">
              <Button
                size="small"
                type="submit"
                label={
                  form.processing
                    ? isEditing
                      ? t("form.saving")
                      : t("form.submitting")
                    : isEditing
                      ? t("form.save")
                      : t("form.submit")
                }
                icon={
                  form.processing ? "pi pi-spin pi-spinner" : "pi pi-calculator"
                }
                disabled={form.processing}
              />
            </div>

            {submitError ? (
              <Message severity="error" text={submitError} className="w-full" />
            ) : null}

            {form.validating ? (
              <div className="flex justify-center py-2">
                <ProgressSpinner style={{ width: "32px", height: "32px" }} />
              </div>
            ) : null}

            {form.processing ? (
              <div className="flex justify-center py-3">
                <ProgressSpinner style={{ width: "40px", height: "40px" }} />
              </div>
            ) : null}
          </form>
        </Card>
      </div>

      <div className="flex flex-col gap-6 lg:col-span-5">
        <QuoteSummary quote={quote} />
      </div>
    </div>
  );
}
