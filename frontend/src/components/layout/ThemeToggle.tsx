"use client";

import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { useThemeStore } from "@/features/quotes/store/themeStore";

export function ThemeToggle({
  showLabel = false,
  className,
}: {
  showLabel?: boolean;
  className?: string;
}) {
  const theme = useThemeStore((state) => state.theme);
  const toggleTheme = useThemeStore((state) => state.toggleTheme);
  const t = useTranslations("theme");
  const label = theme === "light" ? t("dark") : t("light");

  return (
    <Button
      size="small"
      type="button"
      label={showLabel ? label : undefined}
      icon={theme === "light" ? "pi pi-moon" : "pi pi-sun"}
      rounded={!showLabel}
      outlined
      className={className ?? (showLabel ? "w-full justify-center" : undefined)}
      aria-label={label}
      onClick={toggleTheme}
    />
  );
}
