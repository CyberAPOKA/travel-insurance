"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Calendar } from "primereact/calendar";
import { Checkbox } from "primereact/checkbox";
import { Dialog } from "primereact/dialog";
import { FloatLabel } from "primereact/floatlabel";
import { InputText } from "primereact/inputtext";
import { useFormat } from "@/lib/hooks/useFormat";
import { ADD_ONS } from "@/lib/constants";
import type { AddOn } from "../types/quote";
import { buildAddOnOptions } from "../utils/quoteUi";

export interface TravelerInput {
  name: string;
  birth_date: string;
  add_ons: AddOn[];
}

export const emptyTraveler = (): TravelerInput => ({
  name: "",
  birth_date: "",
  add_ons: [],
});

interface TravelerModalProps {
  visible: boolean;
  mode: "add" | "edit";
  initialValue: TravelerInput;
  onHide: () => void;
  onSave: (traveler: TravelerInput) => void;
}

export function TravelerModal({
  visible,
  mode,
  initialValue,
  onHide,
  onSave,
}: TravelerModalProps) {
  const t = useTranslations();
  const { calendarDateFormat, parseApiDate, toApiDate, startOfToday } =
    useFormat();
  const today = startOfToday();
  const addOnOptions = buildAddOnOptions(ADD_ONS, t);
  const [draft, setDraft] = useState<TravelerInput>(initialValue);
  const [errors, setErrors] = useState<{ name?: string; birth_date?: string }>(
    {},
  );

  useEffect(() => {
    if (visible) {
      setDraft(initialValue);
      setErrors({});
    }
  }, [
    visible,
    initialValue.name,
    initialValue.birth_date,
    initialValue.add_ons.join(","),
  ]);

  const toggleAddOn = (addOn: AddOn) => {
    setDraft((current) => ({
      ...current,
      add_ons: current.add_ons.includes(addOn)
        ? current.add_ons.filter((item) => item !== addOn)
        : [...current.add_ons, addOn],
    }));
  };

  const handleSave = () => {
    const nextErrors: { name?: string; birth_date?: string } = {};

    if (!draft.name.trim()) {
      nextErrors.name = t("form.travelerNameRequired");
    }

    if (!draft.birth_date) {
      nextErrors.birth_date = t("form.birthDateRequired");
    }

    if (Object.keys(nextErrors).length > 0) {
      setErrors(nextErrors);
      return;
    }

    onSave({
      name: draft.name.trim(),
      birth_date: draft.birth_date,
      add_ons: draft.add_ons,
    });
  };

  const footer = (
    <div className="flex justify-end gap-2">
      <Button
        size="small"
        type="button"
        label={t("form.cancel")}
        severity="secondary"
        outlined
        onClick={onHide}
      />
      <Button
        size="small"
        type="button"
        label={t("form.saveTraveler")}
        icon="pi pi-check"
        onClick={handleSave}
      />
    </div>
  );

  return (
    <Dialog
      visible={visible}
      header={
        mode === "add"
          ? t("form.addTravelerTitle")
          : t("form.editTravelerTitle")
      }
      modal
      draggable={false}
      className="w-full max-w-lg"
      onHide={onHide}
      footer={footer}
    >
      <div className="space-y-6 mt-6">
        <div>
          <FloatLabel>
            <InputText
              id="traveler-modal-name"
              value={draft.name}
              className={`w-full${errors.name ? " p-invalid" : ""}`}
              onChange={(event) => {
                setDraft((current) => ({
                  ...current,
                  name: event.target.value,
                }));
                if (errors.name) {
                  setErrors((current) => ({ ...current, name: undefined }));
                }
              }}
            />
            <label htmlFor="traveler-modal-name">
              {t("form.travelerName")}
            </label>
          </FloatLabel>
          {errors.name ? (
            <small className="mt-1 flex items-center gap-1 text-red-500">
              <i className="pi pi-exclamation-circle text-xs" />
              {errors.name}
            </small>
          ) : null}
        </div>

        <div>
          <FloatLabel>
            <Calendar
              inputId="traveler-modal-birth"
              value={parseApiDate(draft.birth_date)}
              maxDate={today}
              onChange={(event) => {
                const value = toApiDate(event.value as Date | null);
                setDraft((current) => ({ ...current, birth_date: value }));
                if (errors.birth_date) {
                  setErrors((current) => ({
                    ...current,
                    birth_date: undefined,
                  }));
                }
              }}
              dateFormat={calendarDateFormat}
              showIcon
              className={`w-full${errors.birth_date ? " p-invalid" : ""}`}
            />
            <label htmlFor="traveler-modal-birth">{t("form.birthDate")}</label>
          </FloatLabel>
          {errors.birth_date ? (
            <small className="mt-1 flex items-center gap-1 text-red-500">
              <i className="pi pi-exclamation-circle text-xs" />
              {errors.birth_date}
            </small>
          ) : null}
        </div>

        <div>
          <p className="mb-3 font-medium">{t("form.addOns")}</p>
          <div className="space-y-4">
            {addOnOptions.map((option) => (
              <div key={option.value} className="flex items-center gap-2">
                <Checkbox
                  inputId={`traveler-modal-${option.value}`}
                  checked={draft.add_ons.includes(option.value)}
                  onChange={() => toggleAddOn(option.value)}
                />
                <label htmlFor={`traveler-modal-${option.value}`}>
                  {option.label}
                </label>
              </div>
            ))}
          </div>
        </div>
      </div>
    </Dialog>
  );
}
