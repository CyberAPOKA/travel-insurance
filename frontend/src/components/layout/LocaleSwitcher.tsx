"use client";

import { useLocale, useTranslations } from "next-intl";
import { Dropdown } from "primereact/dropdown";
import { usePathname, useRouter } from "@/i18n/navigation";
import { routing } from "@/i18n/routing";

export function LocaleSwitcher({ className = "w-48" }: { className?: string }) {
  const locale = useLocale();
  const t = useTranslations("locale");
  const router = useRouter();
  const pathname = usePathname();

  const options = routing.locales.map((item) => ({
    label: t(item),
    value: item,
  }));

  return (
    <Dropdown
      value={locale}
      options={options}
      onChange={(event) => router.replace(pathname, { locale: event.value })}
      className={className}
      aria-label="Language"
    />
  );
}
