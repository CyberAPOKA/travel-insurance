import { describe, expect, it } from "vitest";
import {
  formatPixCountdown,
  parsePixExpirationDate,
} from "./pixExpirationCountdown";

describe("pixExpirationCountdown", () => {
  it("parses Asaas expiration datetime format", () => {
    const parsed = parsePixExpirationDate("2026-06-07 23:59:59");

    expect(parsed.getFullYear()).toBe(2026);
    expect(parsed.getMonth()).toBe(5);
    expect(parsed.getDate()).toBe(7);
  });

  it("formats remaining time with days when needed", () => {
    expect(formatPixCountdown(90061 * 1000)).toBe("1d 01:01:01");
  });

  it("formats remaining time under one day", () => {
    expect(formatPixCountdown(3661000)).toBe("01:01:01");
  });

  it("returns zero countdown when expired", () => {
    expect(formatPixCountdown(0)).toBe("00:00:00");
    expect(formatPixCountdown(-1000)).toBe("00:00:00");
  });
});
