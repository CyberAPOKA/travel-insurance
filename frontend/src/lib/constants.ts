export const DESTINATION_ZONES = ["NATIONAL", "AMERICAS", "EUROPE"] as const;

export const ADD_ONS = ["LUGGAGE", "ADVENTURE_SPORTS"] as const;

export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";
