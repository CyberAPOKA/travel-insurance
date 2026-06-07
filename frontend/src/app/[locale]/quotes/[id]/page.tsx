import { setRequestLocale } from "next-intl/server";
import { QuoteDetailPageClient } from "@/features/quotes/components/QuoteDetailPageClient";

type QuoteDetailPageProps = {
  params: Promise<{ locale: string; id: string }>;
};

export default async function QuoteDetailPage({ params }: QuoteDetailPageProps) {
  const { locale, id } = await params;
  setRequestLocale(locale);

  return <QuoteDetailPageClient quoteId={Number(id)} />;
}
