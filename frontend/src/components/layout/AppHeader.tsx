"use client";

import { useState } from "react";
import { useTranslations } from "next-intl";
import { Button } from "primereact/button";
import { AppHeaderActions } from "@/components/layout/AppHeaderActions";
import { LocaleSwitcher } from "@/components/layout/LocaleSwitcher";
import { ThemeColorSwitcher } from "@/components/layout/ThemeColorSwitcher";
import { ThemeToggle } from "@/components/layout/ThemeToggle";

export function AppHeader() {
  const t = useTranslations("header");
  const tApp = useTranslations("app");
  const [menuOpen, setMenuOpen] = useState(false);

  const closeMenu = () => setMenuOpen(false);

  return (
    <header className="mb-4">
      <div className="flex items-center justify-between gap-3">
        <div className="hidden min-w-0 flex-1 lg:block">
          <AppHeaderActions />
        </div>

        <p className="truncate text-sm font-semibold text-slate-800 dark:text-zinc-100 lg:hidden">
          {tApp("title")}
        </p>

        <div className="hidden items-center gap-2 lg:flex">
          <LocaleSwitcher />
          <ThemeColorSwitcher />
          <ThemeToggle />
        </div>

        <div className="lg:hidden">
          <Button
            type="button"
            icon={menuOpen ? "pi pi-times" : "pi pi-bars"}
            rounded
            outlined
            aria-expanded={menuOpen}
            aria-label={menuOpen ? t("closeMenu") : t("openMenu")}
            onClick={() => setMenuOpen((open) => !open)}
          />
        </div>
      </div>

      {menuOpen ? (
        <nav
          aria-label={t("menu")}
          className="mt-4 flex flex-col gap-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:hidden"
        >
          <div className="flex flex-col gap-2">
            <span className="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">
              {t("language")}
            </span>
            <LocaleSwitcher className="w-full" />
          </div>

          <div className="flex flex-col gap-2">
            <span className="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">
              {t("themeMode")}
            </span>
            <ThemeToggle showLabel className="w-full justify-center" />
          </div>

          <div className="flex flex-col gap-2">
            <span className="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">
              {t("themeColor")}
            </span>
            <ThemeColorSwitcher inline />
          </div>

          <AppHeaderActions variant="stacked" onAction={closeMenu} />
        </nav>
      ) : null}
    </header>
  );
}
