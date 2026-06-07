export const PRIME_REACT_VERSION = "10.9.8";

export const LARA_THEME_COLORS = [
  {
    id: "cyan",
    gradient: "linear-gradient(135deg, #22d3ee 0%, #0891b2 100%)",
  },
  {
    id: "teal",
    gradient: "linear-gradient(135deg, #2dd4bf 0%, #0d9488 100%)",
  },
  {
    id: "blue",
    gradient: "linear-gradient(135deg, #60a5fa 0%, #2563eb 100%)",
  },
  {
    id: "indigo",
    gradient: "linear-gradient(135deg, #818cf8 0%, #4f46e5 100%)",
  },
  {
    id: "purple",
    gradient: "linear-gradient(135deg, #c084fc 0%, #9333ea 100%)",
  },
  {
    id: "amber",
    gradient: "linear-gradient(135deg, #fbbf24 0%, #d97706 100%)",
  },
  {
    id: "green",
    gradient: "linear-gradient(135deg, #4ade80 0%, #16a34a 100%)",
  },
  {
    id: "pink",
    gradient: "linear-gradient(135deg, #f472b6 0%, #db2777 100%)",
  },
] as const;

export type LaraThemeColor = (typeof LARA_THEME_COLORS)[number]["id"];

export type ThemeMode = "light" | "dark";

export function getPrimeThemeStylesheet(
  mode: ThemeMode,
  color: LaraThemeColor,
): string {
  return `https://cdn.jsdelivr.net/npm/primereact@${PRIME_REACT_VERSION}/resources/themes/lara-${mode}-${color}/theme.css`;
}

export function isLaraThemeColor(value: string): value is LaraThemeColor {
  return LARA_THEME_COLORS.some((item) => item.id === value);
}
