import type { QuoteWarning } from "../types/quote";

type WarningTranslator = (
  key: string,
  values?: Record<string, string | number>,
) => string;

const LEGACY_ADVENTURE_SPORTS_WARNING =
  /^ADVENTURE_SPORTS was not applied to (.+): outside the allowed age range \((\d+)-(\d+)\)\.$/;

function parseLegacyWarning(warning: string): QuoteWarning | null {
  const match = warning.match(LEGACY_ADVENTURE_SPORTS_WARNING);

  if (!match) {
    return null;
  }

  return {
    code: "adventure_sports_age_out_of_range",
    params: {
      travelerName: match[1],
      minAge: Number(match[2]),
      maxAge: Number(match[3]),
    },
  };
}

export function translateQuoteWarning(
  warning: QuoteWarning,
  t: WarningTranslator,
): string {
  const normalized =
    typeof warning === "string" ? parseLegacyWarning(warning) ?? warning : warning;

  if (typeof normalized === "string") {
    return normalized;
  }

  if (normalized.code === "adventure_sports_age_out_of_range") {
    return t("warnings.adventure_sports_age_out_of_range", {
      addOn: t("addOns.ADVENTURE_SPORTS"),
      travelerName: String(normalized.params.travelerName ?? ""),
      minAge: Number(normalized.params.minAge),
      maxAge: Number(normalized.params.maxAge),
    });
  }

  return normalized.code;
}

export function quoteWarningKey(warning: QuoteWarning, index: number): string {
  if (typeof warning === "string") {
    return `legacy-${index}-${warning}`;
  }

  return `${warning.code}-${index}-${JSON.stringify(warning.params)}`;
}
