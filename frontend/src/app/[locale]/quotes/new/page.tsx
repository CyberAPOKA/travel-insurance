import { setRequestLocale } from "next-intl/server";
import { NewQuotePageClient } from "@/features/quotes/components/NewQuotePageClient";

interface NewQuotePageProps {
  params: Promise<{ locale: string }>;
}

export default async function NewQuotePage({ params }: NewQuotePageProps) {
  const { locale } = await params;
  setRequestLocale(locale);

  return <NewQuotePageClient />;
}
