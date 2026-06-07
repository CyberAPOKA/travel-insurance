import { describe, expect, it } from "vitest";
import { translateQuoteWarning, quoteWarningKey } from "./quoteWarnings";

const t = (key: string, values?: Record<string, string | number>) => {
  if (key === "warnings.adventure_sports_age_out_of_range") {
    return `${values?.addOn} not applied to ${values?.travelerName} (${values?.minAge}-${values?.maxAge})`;
  }

  if (key === "addOns.ADVENTURE_SPORTS") {
    return "Adventure sports";
  }

  return key;
};

describe("translateQuoteWarning", () => {
  it("translates structured warnings", () => {
    const message = translateQuoteWarning(
      {
        code: "adventure_sports_age_out_of_range",
        params: {
          travelerName: "João",
          minAge: 18,
          maxAge: 64,
        },
      },
      t,
    );

    expect(message).toBe("Adventure sports not applied to João (18-64)");
  });

  it("translates legacy English warning strings", () => {
    const message = translateQuoteWarning(
      "ADVENTURE_SPORTS was not applied to John: outside the allowed age range (18-64).",
      t,
    );

    expect(message).toBe("Adventure sports not applied to John (18-64)");
  });
});

describe("quoteWarningKey", () => {
  it("builds stable keys for structured warnings", () => {
    expect(
      quoteWarningKey(
        {
          code: "adventure_sports_age_out_of_range",
          params: { travelerName: "Ana", minAge: 18, maxAge: 64 },
        },
        0,
      ),
    ).toContain("adventure_sports_age_out_of_range");
  });
});
