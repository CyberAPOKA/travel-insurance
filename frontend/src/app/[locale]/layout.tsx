import { notFound } from "next/navigation";
import { NextIntlClientProvider } from "next-intl";
import { getMessages, setRequestLocale } from "next-intl/server";
import { AppHeader } from "@/components/layout/AppHeader";
import { PrimeProvider, ThemeProvider } from "@/components/providers/PrimeProvider";
import { routing } from "@/i18n/routing";

interface LocaleLayoutProps {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export default async function LocaleLayout({
  children,
  params,
}: LocaleLayoutProps) {
  const { locale } = await params;

  if (!routing.locales.includes(locale as "en" | "pt")) {
    notFound();
  }

  setRequestLocale(locale);
  const messages = await getMessages();

  return (
    <NextIntlClientProvider messages={messages}>
      <PrimeProvider>
        <ThemeProvider>
          <div className="min-h-screen bg-slate-50 dark:bg-zinc-950">
            <div className="mx-auto w-full max-w-screen-2xl px-4 py-4 lg:px-6">
              <AppHeader />
              <main>{children}</main>
            </div>
          </div>
        </ThemeProvider>
      </PrimeProvider>
    </NextIntlClientProvider>
  );
}
