export function parsePixExpirationDate(value: string): Date {
  const normalized = value.includes("T") ? value : value.replace(" ", "T");

  const parsed = new Date(normalized);

  if (Number.isNaN(parsed.getTime())) {
    return new Date(value);
  }

  return parsed;
}

export function formatPixCountdown(remainingMs: number): string {
  if (remainingMs <= 0) {
    return "00:00:00";
  }

  const totalSeconds = Math.floor(remainingMs / 1000);
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  const pad = (value: number) => String(value).padStart(2, "0");

  if (days > 0) {
    return `${days}d ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
  }

  return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
}
