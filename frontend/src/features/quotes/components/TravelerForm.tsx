"use client";

import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { Calendar } from "primereact/calendar";
import { Checkbox } from "primereact/checkbox";
import { FloatLabel } from "primereact/floatlabel";
import { InputText } from "primereact/inputtext";
import { Panel } from "primereact/panel";
import {
  FieldError,
  fieldClassName,
  type FormLike,
} from "@/components/ui/FieldError";
import { useFormat } from "@/lib/hooks/useFormat";
import { ADD_ONS } from "@/lib/constants";
import type { AddOn } from "../types/quote";
import { buildAddOnOptions } from "../utils/quoteUi";

interface TravelerFormItem {
  name: string;
  birth_date: string;
  add_ons: AddOn[];
}

interface TravelerFormProps {
  form: FormLike & {
    data: { travelers: TravelerFormItem[] };
    setData: (key: string, value: unknown) => unknown;
    validate: (name: string) => unknown;
  };
  traveler: TravelerFormItem;
  index: number;
  canRemove: boolean;
  onRemove: () => void;
}

export type { TravelerFormProps };

export function TravelerForm({
  form,
  traveler,
  index,
  canRemove,
  onRemove,
}: TravelerFormProps) {
  const t = useTranslations();
  const { calendarDateFormat, parseApiDate, toApiDate, startOfToday } =
    useFormat();
  const today = startOfToday();
  const addOnOptions = buildAddOnOptions(ADD_ONS, t);
  const nameField = `travelers.${index}.name`;
  const birthDateField = `travelers.${index}.birth_date`;

  const header = (
    <div className="flex w-full items-center justify-between gap-2">
      <span className="font-semibold">
        {t("form.travelers")} {index + 1}
      </span>
      {canRemove ? (
        <Button
          size="small"
          type="button"
          label={t("form.removeTraveler")}
          icon="pi pi-trash"
          severity="danger"
          text
          onClick={onRemove}
        />
      ) : null}
    </div>
  );

  const toggleAddOn = (addOn: AddOn) => {
    const currentAddOns = form.data.travelers[index]?.add_ons ?? [];
    const hasAddOn = currentAddOns.includes(addOn);
    const updatedTravelers = [...form.data.travelers];

    updatedTravelers[index] = {
      ...updatedTravelers[index],
      add_ons: hasAddOn
        ? currentAddOns.filter((item) => item !== addOn)
        : [...currentAddOns, addOn],
    };

    form.setData("travelers", updatedTravelers);
  };

  return (
    <Panel header={header} className="mb-3">
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 mt-2">
        <div>
          <FloatLabel>
            <InputText
              id={`traveler-name-${index}`}
              value={traveler.name}
              className={fieldClassName(form, nameField)}
              onChange={(event) => {
                form.setData(nameField, event.target.value);
              }}
              onBlur={() => form.validate(nameField)}
            />
            <label htmlFor={`traveler-name-${index}`}>
              {t("form.travelerName")}
            </label>
          </FloatLabel>
          <FieldError form={form} name={nameField} />
        </div>
        <div>
          <FloatLabel>
            <Calendar
              inputId={`traveler-birth-${index}`}
              value={parseApiDate(traveler.birth_date)}
              maxDate={today}
              onChange={(event) => {
                const value = toApiDate(event.value as Date | null);
                form.setData(birthDateField, value);
                if (value) {
                  form.validate(birthDateField);
                }
              }}
              dateFormat={calendarDateFormat}
              showIcon
              className={fieldClassName(form, birthDateField)}
            />
            <label htmlFor={`traveler-birth-${index}`}>
              {t("form.birthDate")}
            </label>
          </FloatLabel>
          <FieldError form={form} name={birthDateField} />
        </div>
      </div>

      <div className="mt-4">
        <p className="mb-3 font-medium">{t("form.addOns")}</p>
        <div className="space-y-4">
          {addOnOptions.map((option) => (
            <div key={option.value} className="flex items-center gap-2">
              <Checkbox
                inputId={`traveler-${index}-${option.value}`}
                checked={traveler.add_ons.includes(option.value)}
                onChange={() => toggleAddOn(option.value)}
              />
              <label htmlFor={`traveler-${index}-${option.value}`}>
                {option.label}
              </label>
            </div>
          ))}
        </div>
      </div>
    </Panel>
  );
}
