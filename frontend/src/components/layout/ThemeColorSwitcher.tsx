"use client";

import { useRef } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { OverlayPanel } from "primereact/overlaypanel";
import { LARA_THEME_COLORS } from "@/lib/primeThemes";
import { useThemeStore } from "@/features/quotes/store/themeStore";

export function ThemeColorSwitcher({ inline = false }: { inline?: boolean }) {
  const overlayRef = useRef<OverlayPanel>(null);
  const color = useThemeStore((state) => state.color);
  const setColor = useThemeStore((state) => state.setColor);
  const t = useTranslations("theme");

  const colorGrid = (
    <div className={inline ? "w-full" : "w-64"}>
      <div className="mb-3 flex items-center gap-2">
        <span
          className="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold text-white"
          style={{
            background: LARA_THEME_COLORS.find((item) => item.id === color)?.gradient,
          }}
        >
          L
        </span>
        <span className="font-semibold">{t("lara")}</span>
      </div>

      <div className="grid grid-cols-4 gap-3">
        {LARA_THEME_COLORS.map((item) => {
          const isSelected = color === item.id;

          return (
            <Button
              size="small"
              key={item.id}
              type="button"
              title={t(`colors.${item.id}`)}
              aria-label={t(`colors.${item.id}`)}
              aria-pressed={isSelected}
              className={`h-9 rounded-full border-2 transition-transform ${
                isSelected
                  ? "scale-105 border-slate-900 dark:border-white"
                  : "border-transparent hover:scale-105"
              }`}
              style={{ background: item.gradient }}
              onClick={() => {
                setColor(item.id);
                overlayRef.current?.hide();
              }}
            />
          );
        })}
      </div>
    </div>
  );

  if (inline) {
    return colorGrid;
  }

  return (
    <>
      <Button
        size="small"
        type="button"
        icon="pi pi-palette"
        rounded
        outlined
        aria-label={t("color")}
        tooltip={t("color")}
        tooltipOptions={{ position: "bottom" }}
        onClick={(event) => overlayRef.current?.toggle(event)}
      />

      <OverlayPanel ref={overlayRef} dismissable showCloseIcon>
        {colorGrid}
      </OverlayPanel>
    </>
  );
}
