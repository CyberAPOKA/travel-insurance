import type { Metadata } from "next";
import { cookies } from "next/headers";
import { Geist, Geist_Mono } from "next/font/google";
import "primereact/resources/primereact.min.css";
import "primeicons/primeicons.css";
import { resolveTheme, THEME_COOKIE_NAME } from "@/lib/themeCookie";
import {
  getPrimeThemeHref,
  PRIME_THEME_LINK_ID,
} from "@/lib/themeInit";
import "./globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "Travel Insurance Quote",
  description: "Travel insurance quote calculator",
};

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const cookieStore = await cookies();
  const themeCookie = cookieStore.get(THEME_COOKIE_NAME)?.value;
  const { theme, color } = resolveTheme(
    themeCookie ? decodeURIComponent(themeCookie) : undefined,
  );
  const themeHref = getPrimeThemeHref(theme, color);

  return (
    <html
      lang="en"
      data-theme={theme}
      data-theme-color={color}
      suppressHydrationWarning
      className={`${geistSans.variable} ${geistMono.variable} h-full antialiased${
        theme === "dark" ? " dark" : ""
      }`}
    >
      <body
        className="min-h-full bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100"
        suppressHydrationWarning
      >
        <link
          id={PRIME_THEME_LINK_ID}
          rel="stylesheet"
          href={themeHref}
          precedence="default"
        />
        {children}
      </body>
    </html>
  );
}
