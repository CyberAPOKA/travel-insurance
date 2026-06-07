import { useEffect, useState } from "react";
import {
  formatPixCountdown,
  parsePixExpirationDate,
} from "../utils/pixExpirationCountdown";

export function usePixExpirationCountdown(
  expirationDate: string | null | undefined,
) {
  const [remainingMs, setRemainingMs] = useState<number | null>(null);

  useEffect(() => {
    if (!expirationDate) {
      setRemainingMs(null);
      return;
    }

    const expiresAt = parsePixExpirationDate(expirationDate).getTime();

    const tick = () => {
      setRemainingMs(Math.max(0, expiresAt - Date.now()));
    };

    tick();
    const intervalId = window.setInterval(tick, 1000);

    return () => window.clearInterval(intervalId);
  }, [expirationDate]);

  if (remainingMs === null) {
    return null;
  }

  return {
    expired: remainingMs <= 0,
    label: formatPixCountdown(remainingMs),
  };
}
