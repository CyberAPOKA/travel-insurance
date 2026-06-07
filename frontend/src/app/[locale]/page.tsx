import { setRequestLocale } from "next-intl/server";
import { HomePageClient } from "@/features/home/components/HomePageClient";

interface HomePageProps {
  params: Promise<{ locale: string }>;
}

export default async function HomePage({ params }: HomePageProps) {
  const { locale } = await params;
  setRequestLocale(locale);

  return <HomePageClient />;
}
