"use client";

import { useRef } from "react";
import { Calendar, type CalendarProps } from "primereact/calendar";
import { getCalendarInputMask } from "@/lib/format";
import { useFormat } from "@/lib/hooks/useFormat";

export type DateCalendarProps = Omit<CalendarProps, "dateFormat" | "mask">;

export function DateCalendar({
  showIcon = true,
  showOnFocus = true,
  onShow,
  ...props
}: DateCalendarProps) {
  const { calendarDateFormat } = useFormat();
  const calendarRef = useRef<Calendar>(null);

  const handleShow = () => {
    requestAnimationFrame(() => {
      calendarRef.current?.getInput()?.focus();
    });
    onShow?.();
  };

  return (
    <Calendar
      ref={calendarRef}
      {...props}
      dateFormat={calendarDateFormat}
      mask={getCalendarInputMask()}
      keepInvalid
      showIcon={showIcon}
      showOnFocus={showOnFocus}
      onShow={handleShow}
    />
  );
}
