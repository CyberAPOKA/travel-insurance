import { redirect } from "@/i18n/navigation";

interface RegisterPageProps {
  params: Promise<{ locale: string }>;
}

export default async function RegisterPage({ params }: RegisterPageProps) {
  const { locale } = await params;
  redirect({ href: "/", locale });
}
